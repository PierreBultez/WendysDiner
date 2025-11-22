<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CartService
{
    protected string $sessionKey = 'cart';

    public function get(): array
    {
        return Session::get($this->sessionKey, []);
    }

    public function add(array $item): void
    {
        $cart = $this->get();

        // If item with same ID exists, just increment quantity (unless it has specific unique logic handled by caller)
        // The POS logic generates unique IDs for complex items (burgers with options), so we trust the ID provided.
        if (isset($cart[$item['id']])) {
            $cart[$item['id']]['quantity'] += $item['quantity'];
        } else {
            $cart[$item['id']] = $item;
        }

        Session::put($this->sessionKey, $cart);
    }

    public function remove(string $id): void
    {
        $cart = $this->get();
        unset($cart[$id]);
        Session::put($this->sessionKey, $cart);
    }

    public function updateQuantity(string $id, int $quantity): void
    {
        $cart = $this->get();
        if (isset($cart[$id])) {
            if ($quantity > 0) {
                $cart[$id]['quantity'] = $quantity;
            } else {
                unset($cart[$id]);
            }
            Session::put($this->sessionKey, $cart);
        }
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }

    public function total(): float
    {
        return collect($this->get())->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    public function count(): int
    {
        return collect($this->get())->sum('quantity');
    }
}
