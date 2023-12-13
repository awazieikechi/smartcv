<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PdfDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\PdfToText\Pdf;

class StorePdfDocumentAsText implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $file_name;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $file_name)
    {
        $this->filename = $file_name;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        PdfDocument::create([
            'title' => 'Pdf Document Saved',
            'content' => Str::limit(
                Pdf::getText(
                    Storage::disk('local')->path($this->filename),
                ),
                60000,
            ),
            'user_id' => $this->user
        ]);
    }
}
