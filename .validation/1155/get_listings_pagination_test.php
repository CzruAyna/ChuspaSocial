<?php
namespace local_chuspasocial\external;

use advanced_testcase;
use core_external\external_api;
use local_chuspasocial\local\listing_service;
use local_chuspasocial\local\persistent\listing;

/**
 * @covers \local_chuspasocial\external\get_listings
 */
final class get_listings_pagination_test extends advanced_testcase {
    private function offer(int $seller, string $title, string $author, array $courses, string $status = 'available'): int {
        global $DB;
        $listing = new listing(0, (object) [
            'userid' => $seller,
            'title' => $title,
            'author' => $author,
            'status' => $status,
        ]);
        $listing->create();
        $id = (int) $listing->get('id');
        $DB->set_field('local_chuspasocial_listing', 'timecreated', 1760000000, ['id' => $id]);
        listing_service::set_courses($id, $courses);
        return $id;
    }

    private function request(array $arguments): array {
        $previous = $_POST;
        $_POST['sesskey'] = sesskey();
        try {
            $response = external_api::call_external_function('local_chuspasocial_get_listings', $arguments);
            $this->assertFalse($response['error'], $response['exception']->message ?? '');
            return $response['data']['listings'];
        } finally {
            $_POST = $previous;
        }
    }

    public function test_category_is_applied_before_offset_with_stable_order(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $target = $generator->create_category();
        $other = $generator->create_category();
        $course = $generator->create_course(['category' => $target->id]);
        $second = $generator->create_course(['category' => $target->id]);
        $outsider = $generator->create_course(['category' => $target->id]);
        $unrelated = $generator->create_course(['category' => $other->id]);
        $viewer = $generator->create_user();
        $seller = $generator->create_user();
        foreach ([$course, $second, $unrelated] as $enrolled) {
            $generator->enrol_user($viewer->id, $enrolled->id, 'student');
        }
        $old = $this->offer($seller->id, 'Calculus I', 'Stewart', [$course->id]);
        $new = $this->offer($seller->id, 'Calculus II', 'Stewart', [$course->id, $second->id]);
        $this->offer($seller->id, 'Calculus outside', 'Stewart', [$unrelated->id, $outsider->id]);
        $this->offer($seller->id, 'Calculus sold', 'Stewart', [$course->id], 'sold');
        $this->setUser($viewer);

        $result = $this->request(['categoryid' => (int) $target->id]);
        $this->assertSame([$new, $old], array_column($result, 'id'));
        $this->assertSame([$new], array_column(get_listings::execute(categoryid: $target->id, limit: 1)['listings'], 'id'));
        $this->assertSame([$old], array_column(get_listings::execute(categoryid: $target->id, limit: 1, offset: 1)['listings'], 'id'));
        $this->assertSame([], get_listings::execute(categoryid: $target->id, offset: 2)['listings']);
        $this->assertSame([$old], array_column(get_listings::execute(page: 1, perpage: 1, categoryid: $target->id)['listings'], 'id'));

        $this->setUser($seller);
        $this->assertSame([], get_listings::execute(categoryid: $target->id, limit: 1)['listings']);
    }

    public function test_search_filters_title_and_author_by_course_category(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $target = $generator->create_category();
        $other = $generator->create_category();
        $course = $generator->create_course(['category' => $target->id]);
        $unrelated = $generator->create_course(['category' => $other->id]);
        $seller = $generator->create_user();
        $title = $this->offer($seller->id, 'Algebra', 'Stewart', [$course->id]);
        $author = $this->offer($seller->id, 'Geometry', 'Algebra Group', [$course->id], 'sold');
        $outside = $this->offer($seller->id, 'Algebra II', 'Stewart', [$unrelated->id]);
        $unlinked = $this->offer($seller->id, 'Algebra III', 'Stewart', []);
        $this->setUser($generator->create_user());

        $result = get_listings::execute('ALGebr', categoryid: $target->id);
        $this->assertSame([$author, $title], array_column($result['listings'], 'id'));
        $this->assertSame($result, external_api::clean_returnvalue(get_listings::execute_returns(), $result));
        $this->assertSame([$unlinked, $outside], array_column(get_listings::execute('algebra', limit: 2)['listings'], 'id'));
        $this->assertSame([$title], array_column(get_listings::execute('algebra', 1, 2, limit: 1, offset: 3)['listings'], 'id'));
        $this->assertSame([], get_listings::execute('algebra', categoryid: $target->id + 10000)['listings']);
    }

    public function test_defaults_and_legacy_page(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $seller = $generator->create_user();
        $ids = [];
        for ($i = 0; $i < 21; $i++) {
            $ids[] = $this->offer($seller->id, "Physics $i", 'Halliday', []);
        }
        $this->setUser($generator->create_user());

        $result = $this->request(['search' => 'physics']);
        $this->assertSame(array_slice(array_reverse($ids), 0, 20), array_column($result, 'id'));
        $this->assertSame([$ids[0]], array_column(get_listings::execute('physics', 1)['listings'], 'id'));
        $this->assertSame([$ids[0]], array_column(get_listings::execute('physics', offset: 20)['listings'], 'id'));
    }

    /**
     * @dataProvider invalid_arguments
     */
    public function test_invalid_pagination(array $arguments): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());
        $this->expectException(\invalid_parameter_exception::class);
        get_listings::execute(...$arguments);
    }

    public static function invalid_arguments(): array {
        return [
            'negative category' => [['categoryid' => -1]],
            'negative offset' => [['offset' => -1]],
            'zero limit' => [['limit' => 0]],
            'limit above maximum' => [['limit' => 51]],
            'legacy overflow' => [['page' => PHP_INT_MAX]],
        ];
    }
}
