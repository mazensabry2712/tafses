<?php

namespace App\Support;

class Permissions
{
    public const MANAGE_USERS = 'manage_users';
    public const MANAGE_MASTER_DATA = 'manage_master_data';
    public const RECEIVE_LOADS = 'receive_loads';
    public const MANAGE_COLD_STORES = 'manage_cold_stores';
    public const MANAGE_CUSTODY = 'manage_custody';
    public const PROCESS_POMEGRANATES = 'process_pomegranates';
    public const MANAGE_SUPPLIERS = 'manage_suppliers';
    public const MANAGE_CUSTOMERS = 'manage_customers';
    public const MANAGE_SALES = 'manage_sales';
    public const MANAGE_PAYMENTS = 'manage_payments';
    public const VIEW_REPORTS = 'view_reports';
    public const VIEW_AUDIT = 'view_audit';

    public const ALL = [
        self::MANAGE_USERS,
        self::MANAGE_MASTER_DATA,
        self::RECEIVE_LOADS,
        self::MANAGE_COLD_STORES,
        self::MANAGE_CUSTODY,
        self::PROCESS_POMEGRANATES,
        self::MANAGE_SUPPLIERS,
        self::MANAGE_CUSTOMERS,
        self::MANAGE_SALES,
        self::MANAGE_PAYMENTS,
        self::VIEW_REPORTS,
        self::VIEW_AUDIT,
    ];

    public static function forRole(string $role): array
    {
        return match ($role) {
            'admin' => self::ALL,
            'manager' => [
                self::MANAGE_MASTER_DATA,
                self::RECEIVE_LOADS,
                self::MANAGE_COLD_STORES,
                self::MANAGE_CUSTODY,
                self::PROCESS_POMEGRANATES,
                self::MANAGE_SUPPLIERS,
                self::MANAGE_CUSTOMERS,
                self::MANAGE_SALES,
                self::MANAGE_PAYMENTS,
                self::VIEW_REPORTS,
                self::VIEW_AUDIT,
            ],
            'storekeeper' => [
                self::RECEIVE_LOADS,
                self::MANAGE_COLD_STORES,
                self::MANAGE_CUSTODY,
                self::PROCESS_POMEGRANATES,
                self::VIEW_REPORTS,
            ],
            'accountant' => [
                self::MANAGE_SUPPLIERS,
                self::MANAGE_CUSTOMERS,
                self::MANAGE_SALES,
                self::MANAGE_PAYMENTS,
                self::VIEW_REPORTS,
            ],
            'worker' => [
                self::PROCESS_POMEGRANATES,
            ],
            default => [],
        };
    }
}
