<?php

return [
    [
        'title' => 'Trang chủ',
        'route' => 'dashboard',
        'icon'  => 'home.png',
    ],
    [
        'title' => 'Giao dịch',
        'route' => 'transactions.index',
        'icon'  => 'transaction.png',
    ],
    [
        'title' => 'Ngân sách',
        'route' => 'budgets.index',
        'icon'  => 'asset-allocation.png',
    ],
    [
        'title' => 'Danh mục',
        'route' => 'categories.index',
        'icon'  => 'category.png',
    ],
    [
        'title' => 'Ví',
        'route' => 'money-wallets.index', 
        'icon'  => 'wallet.png',
    ],
    [
        'title' => 'Quy đổi tiền',
        'route' => 'currency.index',
        'icon'  => 'exchange.png',
    ],
    [
        'title' => 'Hội nhóm',
        'route' => 'groups.index',
        'icon'  => 'coworking.png',
    ],
    [
        'title' => 'Du lịch',
        'route' => null, 
        'icon'  => 'travel.png',
    ],
    [
        'title' => 'Mua sắm',
        'route' => null,
        'icon'  => 'shopping.png',
    ],
    [
        'title' => 'Thuế TNCN',
        'route' => null,
        'icon'  => 'tax.png',
    ],
    [
        'title' => 'Quản lý vay',
        'icon'  => 'loan.png',
        'children' => [
            ['title' => 'Cho vay', 'icon' => 'lend.png', 'route' => null],
            ['title' => 'Đã vay', 'icon' => 'borrow.png', 'route' => null],
            ['title' => 'Tính lãi vay', 'icon' => 'interest.png', 'route' => null],
        ]
    ],
    [
        'title' => 'Ghi chép nhanh',
        'icon'  => 'note.png',
        'children' => [
            ['title' => 'Trích xuất hóa đơn', 'icon' => 'receipt.png', 'route' => null],
            ['title' => 'Ghi chép mẫu', 'icon' => 'template.png', 'route' => null],
            ['title' => 'Ghi chép định kỳ', 'icon' => 'repeat.png', 'route' => null],
        ]
    ],
    [
        'title' => 'Cài đặt',
        'route' => 'settings.index',
        'icon'  => 'settings.png',
    ],
];