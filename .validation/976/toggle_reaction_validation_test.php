<?php
namespace local_chuspasocial\external;

use advanced_testcase;
use core_external\external_api;

/**
 * @covers \local_chuspasocial\external\toggle_reaction
 */
final class toggle_reaction_validation_test extends advanced_testcase {
    private function create_post(): int {
        global $DB;
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);
        return (int) $DB->insert_record('local_chuspasocial_post', (object) [
            'userid' => $user->id,
            'courseid' => $course->id,
            'type' => 'post',
            'content' => 'Test post',
            'contentformat' => FORMAT_HTML,
            'hidden' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
    }

    private function request(array $arguments): array {
        $previous = $_POST;
        $_POST['sesskey'] = sesskey();
        try {
            return external_api::call_external_function('local_chuspasocial_toggle_reaction', $arguments, true);
        } finally {
            $_POST = $previous;
        }
    }

    public function test_default_and_explicit_like_toggle_the_same_reaction(): void {
        global $DB;
        $this->resetAfterTest();
        $postid = $this->create_post();

        $created = $this->request(['postid' => $postid]);
        $this->assertFalse($created['error'], $created['exception']->message ?? '');
        $this->assertSame(['reacted' => true], $created['data']);
        $this->assertSame('like', $DB->get_field('local_chuspasocial_reaction', 'type', ['postid' => $postid]));

        $removed = $this->request(['postid' => $postid, 'reactiontype' => 'like']);
        $this->assertFalse($removed['error'], $removed['exception']->message ?? '');
        $this->assertSame(['reacted' => false], $removed['data']);
        $this->assertFalse($DB->record_exists('local_chuspasocial_reaction', ['postid' => $postid]));
    }

    /**
     * @dataProvider rejected_reactions
     */
    public function test_rejected_types_preserve_database(string $type, string $errorcode): void {
        global $DB;
        $this->resetAfterTest();
        $postid = $this->create_post();
        $request = ['postid' => $postid, 'reactiontype' => $type];

        $empty = $this->request($request);
        $this->assertTrue($empty['error']);
        $this->assertSame($errorcode, $empty['exception']->errorcode);
        $this->assertFalse($DB->record_exists('local_chuspasocial_reaction', ['postid' => $postid]));

        $this->assertSame(['reacted' => true], toggle_reaction::execute($postid));
        $before = $DB->get_record('local_chuspasocial_reaction', ['postid' => $postid], '*', MUST_EXIST);
        $existing = $this->request($request);
        $this->assertTrue($existing['error']);
        $this->assertSame($errorcode, $existing['exception']->errorcode);
        $this->assertEquals($before, $DB->get_record('local_chuspasocial_reaction', ['postid' => $postid], '*', MUST_EXIST));
    }

    public static function rejected_reactions(): array {
        return [
            'unknown type' => ['surprise', 'invalidparameter'],
            'wrong case' => ['LIKE', 'invalidparameter'],
            'empty type' => ['', 'invalidparameter'],
            'whitespace' => [' like ', 'invalidparameter'],
            'recognised celebrate' => ['celebrate', 'notavailable'],
            'recognised insightful' => ['insightful', 'notavailable'],
        ];
    }
}
