<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

class PDFDocument extends Model
{
    use HasFactory, Searchable;

    #[SearchUsingFullText(["title", "content"])]

    public function toSearchableArray()
    {
        return [
            "title" => $this->title,
            "content" => $this->content,
        ];
    }
}
