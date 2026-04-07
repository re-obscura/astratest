<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция: создание таблицы tasks.
 *
 * Поля:
 *  - id:          auto-increment primary key
 *  - user_id:     внешний ключ → users.id (с каскадным удалением)
 *  - title:       заголовок задачи (обязательно)
 *  - description: описание задачи (необязательно)
 *  - status:      enum('pending', 'completed'), по умолчанию 'pending'
 *  - timestamps:  created_at + updated_at
 *
 * Каскадное удаление: если удаляем пользователя — все его задачи тоже удалятся.
 */
return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
