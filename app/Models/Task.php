<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель задачи.
 *
 * Поля:
 *  - title:       заголовок (обязательно, макс 255)
 *  - description: описание (необязательно)
 *  - status:      'pending' или 'completed'
 *
 * Связь: каждая задача принадлежит одному пользователю (belongsTo).
 * fillable — только те поля, которые можно массово присваивать.
 * user_id НЕ в fillable — он проставляется через связь $user->tasks()->create().
 */
class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
    ];

    /**
     * Задача принадлежит пользователю.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
