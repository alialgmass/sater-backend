<?php

namespace App\Support\Traits;

use App\Models\Note;
use ArrayAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait NoteAble
{
    protected array $queuedNotes = [];

    public function getNoteAttribute()
    {
        return $this->lastNote?->note;
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'resource');
    }

    public function lastNote()
    {
        return $this->morphOne(Note::class, 'resource')->latest();
    }

    /**
     * Boot the HasNotes trait for a model.
     */
    public static function bootNoteAble(): void
    {
        self::creating(function () {

            static::created(function (Model $taggableModel) {
                if (count($taggableModel->queuedTags) === 0) {
                    return;
                }

                $taggableModel->attachTags($taggableModel->queuedTags);

                $taggableModel->queuedTags = [];
            });

            static::deleted(function (Model $deletedModel) {
                $tags = $deletedModel->tags()->get();

                $deletedModel->detachTags($tags);
            });
        });

    }

    public function setNotesAttribute(string|array|ArrayAccess|Note $notes)
    {
        if (! $this->exists) {
            $this->queuedNotes = $notes;

            return;
        }

        $this->syncNotes($notes);
    }
}
