<?php

namespace Tests\Feature\Profile;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\Support\CreatesFeedbackFixtures;
use Tests\TestCase;

class FeedbackAttachmentTest extends TestCase
{
    use CreatesFeedbackFixtures, RefreshDatabase;

    public function test_valid_evidence_is_private_and_accessible_only_under_its_owned_report(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $owner = $this->createTestUser();
        $other = $this->createTestUser();

        $this->actingAs($owner)->post(route('profile.feedback.store'), $this->validFeedback([
            'attachments' => [
                UploadedFile::fake()->image('evidence.png'),
                UploadedFile::fake()->image('second.jpg'),
                UploadedFile::fake()->image('third.webp'),
            ],
        ]))->assertRedirect(route('profile.feedback.index'))->assertSessionHasNoErrors();

        $report = DB::table('customer_feedback_reports')->first();
        $attachments = DB::table('customer_feedback_attachments')->where('report_id', $report->id)->get();
        $this->assertCount(3, $attachments);
        foreach ($attachments as $attachment) {
            $this->assertStringStartsWith('customer-feedback/'.$report->id.'/', $attachment->path);
            Storage::disk('local')->assertExists($attachment->path);
            Storage::disk('public')->assertMissing($attachment->path);
            $this->assertGreaterThan(0, $attachment->size_bytes);
            $response = $this->get(route('profile.feedback.attachment', ['report' => $report->id, 'attachment' => $attachment->id]))
                ->assertOk()->assertHeader('content-type', $attachment->mime_type)
                ->assertHeader('X-Content-Type-Options', 'nosniff');
            $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertSame(Storage::disk('local')->get($attachment->path), $response->streamedContent());
        }

        $foreignReportId = $this->createReport($other);
        $attachment = $attachments->first();
        $route = route('profile.feedback.attachment', ['report' => $report->id, 'attachment' => $attachment->id]);
        $this->actingAs($other)->get($route)->assertNotFound();
        $this->get(route('profile.feedback.attachment', ['report' => $foreignReportId, 'attachment' => $attachment->id]))
            ->assertNotFound();

        $this->actingAs($owner);
        Storage::disk('local')->delete($attachment->path);
        $this->get($route)->assertNotFound();
    }

    public function test_oversized_forged_and_excess_evidence_are_rejected_before_any_files_or_reports_are_saved(): void
    {
        Storage::fake('local');
        $this->actingAs($this->createTestUser());
        $cases = [
            ['attachments.0', [UploadedFile::fake()->image('oversized.jpg')->size(5121)]],
            ['attachments.0', [UploadedFile::fake()->image('not-supported.gif')]],
            ['attachments.1', [UploadedFile::fake()->image('fine.png'), UploadedFile::fake()->createWithContent('forged.jpg', '<script>alert(1)</script>')]],
            ['attachments', array_map(fn (int $index) => UploadedFile::fake()->image('image-'.$index.'.png'), range(1, 4))],
        ];

        foreach ($cases as [$field, $attachments]) {
            $this->post(route('profile.feedback.store'), $this->validFeedback(compact('attachments')))
                ->assertSessionHasErrors($field);
        }

        $this->assertDatabaseCount('customer_feedback_reports', 0);
        $this->assertDatabaseCount('customer_feedback_attachments', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_attachment_route_rejects_paths_outside_the_report_upload_directory(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('other/private.png', 'private unrelated contents');
        $owner = $this->createTestUser();
        $reportId = $this->createReport($owner);
        $attachmentId = DB::table('customer_feedback_attachments')->insertGetId([
            'report_id'     => $reportId,
            'path'          => 'other/private.png',
            'original_name' => 'private.png',
            'mime_type'     => 'image/png',
            'size_bytes'    => 26,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $this->actingAs($owner)->get(route('profile.feedback.attachment', ['report' => $reportId, 'attachment' => $attachmentId]))
            ->assertNotFound();
    }

    public function test_failed_second_upload_rolls_back_report_and_removes_the_first_private_file(): void
    {
        $disk = Storage::fake('local');
        $failingDisk = Mockery::mock($disk);
        $uploads = 0;
        $failingDisk->shouldReceive('putFileAs')->twice()->andReturnUsing(
            function ($path, $file, $name, $options) use ($disk, &$uploads) {
                $writtenPath = $disk->putFileAs($path, $file, $name, $options);

                return ++$uploads === 1 ? $writtenPath : false;
            }
        );
        Storage::shouldReceive('disk')->with('local')->andReturn($failingDisk);

        $this->actingAs($this->createTestUser())
            ->from(route('profile.feedback.index'))
            ->post(route('profile.feedback.store'), $this->validFeedback([
                'attachments' => [UploadedFile::fake()->image('first.png'), UploadedFile::fake()->image('second.png')],
            ]))->assertRedirect(route('profile.feedback.index'))
            ->assertSessionHasErrors('attachments');

        $this->assertDatabaseCount('customer_feedback_reports', 0);
        $this->assertDatabaseCount('customer_feedback_attachments', 0);
        $this->assertSame([], $disk->allFiles());
    }
}
