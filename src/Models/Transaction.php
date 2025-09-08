<?php

namespace admin\product_transactions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use admin\users\Models\User;
use admin\products\Models\Order;

class Transaction extends Model
{
    use Sortable, SoftDeletes;
    protected $fillable = [
        'user_id',
        'order_id',
        'payment_gateway',
        'transaction_reference',
        'amount',
        'currency',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public $sortable = ['user', 'payment_gateway','transaction_reference', 'amount', 'status', 'created_at'];

    public function userSortable($query, $direction)
    {
         return $query
        ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
        ->orderByRaw("CONCAT(users.first_name, ' ', users.last_name) {$direction}")
        ->select('transactions.*');
    }

    public function orderSortable($query, $direction)
    {
        return $query->join('orders', 'wishlists.course_id', '=', 'orders.id')
            ->orderBy('orders.title', $direction)
            ->select('wishlists.*');
    }

    public function scopeFilter($query, array $filters)
    {
        $keyword = $filters['keyword'] ?? null;

        if ($keyword) {
            return $query->where(function ($q) use ($keyword) {
                $q->whereHas('user', function ($userQuery) use ($keyword) {
                    $userQuery->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"])
                            ->orWhere('email', 'like', "%{$keyword}%");
                })
                ->orWhere('transaction_reference', 'like', "%{$keyword}%");
            });
        }

        return $query;
    }

     /**
     * filter by status
     */
    public function scopeFilterByStatus($query, $status)
    {
        if (!is_null($status)) {
            return $query->where('status', $status);
        }

        return $query;
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function getPerPageLimit(): int
    {
        return Config::has('get.admin_page_limit')
            ? Config::get('get.admin_page_limit')
            : 10;
    }
}