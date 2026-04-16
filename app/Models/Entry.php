<?php

namespace App\Models;

use Database\Factories\EntryFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Entry extends Model
{
    /** @use HasFactory<EntryFactory> */
    use HasFactory;

    protected $fillable = [
        'feed_id',
        'name',
        'audio_url',
        'image_url',
        'transcription_path',
        'summary',
        'original_summary',
        'chapters',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'chapters' => 'json',
            'published_at' => 'datetime',
        ];
    }

    protected function rssDescription(): Attribute
    {
        return Attribute::make(
            get: function () {
                $aiSummary = $this->summary ?? '';
                $originalSummary = $this->original_summary ?? '';

                $showNotesUrl = route('entries.show', [$this->feed_id, $this->id]);
                $appUrl = url('/');

                $html = $aiSummary ? '<p>'.nl2br(e($aiSummary))."</p>\n\n" : '';
                $html .= "<p>🧙 Episode enhanced by <a href=\"{$appUrl}\">Podcasts Enhancer</a></p>";
                $html .= "<p>👉 <a href=\"{$showNotesUrl}\">Read episode transcription</a></p>\n\n";

                if ($this->chapters) {
                    $html .= "\n\n<h2>Timestamps</h2>\n<ul>\n";
                    foreach ($this->chapters as $chapter) {
                        $time = gmdate($chapter['startTime'] >= 3600 ? 'H:i:s' : 'i:s', (int) $chapter['startTime']);
                        $html .= '    <li>'.$time.' - '.e($chapter['title'])."</li>\n";
                    }
                    $html .= '</ul>';
                }

                if ($originalSummary) {
                    $html .= "\n\n<h2>Original Description</h2>\n\n<p>".nl2br(e($originalSummary)).'</p>';
                }

                return trim($html);
            },
        );
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function jobBatches(): HasMany
    {
        return $this->hasMany(EntryJobBatch::class);
    }

    public function latestJobBatch(): HasOne
    {
        return $this->hasOne(EntryJobBatch::class)->latestOfMany();
    }
}
