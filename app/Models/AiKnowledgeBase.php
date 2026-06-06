<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiKnowledgeBase extends Model
{
    protected $table = 'ai_knowledge_base';

    protected $fillable = [
        'category',
        'title',
        'content',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const CATEGORIES = [
        'product_info' => 'Product Info',
        'faq' => 'FAQ',
        'policy' => 'Policy',
        'greeting' => 'Greeting',
        'objection_handling' => 'Objection Handling',
        'custom' => 'Custom',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
