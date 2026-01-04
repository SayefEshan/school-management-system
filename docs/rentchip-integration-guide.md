# RentChip Integration Guide - Renter Pay Platform

## Table of Contents
1. [System Overview](#system-overview)
2. [Architecture Design](#architecture-design)
3. [Module Structure](#module-structure)
4. [Database Schema](#database-schema)
5. [Blockchain Integration](#blockchain-integration)
6. [Payment Flows](#payment-flows)
7. [Security Considerations](#security-considerations)
8. [Implementation Phases](#implementation-phases)
9. [API Design](#api-design)
10. [Testing Strategy](#testing-strategy)
11. [AI Implementation Guidelines](#ai-implementation-guidelines)

---

## System Overview

### Project: Renter Pay - Property Management Platform with Crypto Payments

**Core Concept**: A comprehensive property management system where rent and transactions are conducted using RentChip, a custom cryptocurrency deployed on the Polygon network.

### Stakeholders
1. **Landlord** - Property owners who receive rent payments
2. **Agent** - Property managers who facilitate transactions
3. **Service Vendor** - Service providers for maintenance and repairs
4. **Tenant** - Renters who pay rent and service fees

### Key Features
- **Primary Currency**: RentChip (custom ERC-20 token on Polygon)
- **Custodial System**: Platform manages crypto wallets for all users
- **Rent Payments**: Conducted exclusively in RentChip
- **Fiat Integration**: Deposit/withdraw via third-party payment gateways
- **Multi-stakeholder**: Support for 4 distinct user types with different permissions

---

## Architecture Design

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                      Laravel Application                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │   Wallet    │  │  RentChip   │  │   Payment   │             │
│  │   Module    │  │   Module    │  │   Gateway   │             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
│         │                 │                 │                    │
│         └─────────────────┴─────────────────┘                    │
│                           │                                       │
│  ┌────────────────────────┴────────────────────────┐            │
│  │         Blockchain Service Layer                │            │
│  │  (Web3, Polygon RPC, Smart Contract Interface) │            │
│  └─────────────────────────────────────────────────┘            │
│                           │                                       │
├───────────────────────────┼───────────────────────────────────────┤
│                           │                                       │
│  External Services:       │                                       │
│  - Polygon Network        │                                       │
│  - Payment Gateways (Stripe/PayPal)                              │
│  - KYC/AML Services                                               │
│  - Price Oracle (for fiat conversion)                             │
└───────────────────────────────────────────────────────────────────┘
```

### Technology Stack
- **Backend**: Laravel 12 (PHP 8.4)
- **Blockchain**: Polygon Network (Layer 2 Ethereum)
- **Smart Contract**: ERC-20 RentChip Token
- **Web3 Library**: web3.php or equivalent
- **Payment Gateways**: Stripe, PayPal (for fiat conversion)
- **Database**: MySQL/PostgreSQL
- **Queue**: Redis (for blockchain transactions)
- **Cache**: Redis

---

## Module Structure

Following the existing modular architecture, we need to create the following modules:

### 1. Wallet Module (`Modules/Wallet`)

**Purpose**: Manage custodial wallets for all users

**Responsibilities**:
- Create custodial wallets for users
- Store encrypted private keys
- Manage wallet balances (RentChip + fiat equivalent)
- Handle wallet security and recovery
- Multi-signature support for high-value transactions
- Wallet transaction history

**Key Components**:
```
Modules/Wallet/
├── app/
│   ├── Models/
│   │   ├── Wallet.php               # Main wallet model
│   │   ├── WalletTransaction.php    # Transaction history
│   │   └── WalletAddress.php        # Blockchain addresses
│   ├── Services/
│   │   ├── WalletService.php        # Wallet creation/management
│   │   ├── KeyManagementService.php # Encrypted key storage
│   │   └── BalanceService.php       # Balance tracking
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── WalletController.php
│   │   │   └── Api/WalletApiController.php
│   │   └── Requests/
│   │       ├── CreateWalletRequest.php
│   │       └── WalletTransactionRequest.php
│   ├── Jobs/
│   │   ├── CreateWalletJob.php
│   │   └── SyncWalletBalanceJob.php
│   └── Observers/
│       └── WalletObserver.php
├── database/
│   ├── migrations/
│   │   ├── create_wallets_table.php
│   │   ├── create_wallet_transactions_table.php
│   │   └── create_wallet_addresses_table.php
│   ├── factories/
│   │   └── WalletFactory.php
│   └── seeders/
│       └── WalletSeeder.php
├── routes/
│   ├── api.php
│   └── web.php
└── tests/
    ├── Feature/
    │   ├── WalletCreationTest.php
    │   └── WalletTransactionTest.php
    └── Unit/
        └── WalletServiceTest.php
```

### 2. RentChip Module (`Modules/RentChip`)

**Purpose**: Handle all RentChip token operations

**Responsibilities**:
- Interface with RentChip smart contract
- Transfer RentChip between wallets
- Check balances on Polygon network
- Handle token allowances
- Gas fee management
- Transaction confirmation tracking

**Key Components**:
```
Modules/RentChip/
├── app/
│   ├── Models/
│   │   ├── RentChipTransaction.php  # Blockchain transactions
│   │   ├── TransactionStatus.php    # Transaction states
│   │   └── GasFee.php               # Gas tracking
│   ├── Services/
│   │   ├── RentChipService.php      # Main token service
│   │   ├── BlockchainService.php    # Polygon interaction
│   │   ├── SmartContractService.php # Contract ABI interface
│   │   └── GasEstimationService.php # Gas calculations
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── RentChipController.php
│   │   │   └── Api/RentChipApiController.php
│   │   └── Requests/
│   │       └── TransferRentChipRequest.php
│   ├── Jobs/
│   │   ├── ProcessRentChipTransferJob.php
│   │   ├── ConfirmTransactionJob.php
│   │   └── UpdateGasPriceJob.php
│   ├── Listeners/
│   │   └── RentChipTransactionListener.php
│   └── Enum/
│       └── TransactionStatusEnum.php
├── config/
│   └── rentchip.php                 # Contract address, ABI, etc.
├── database/
│   ├── migrations/
│   │   ├── create_rentchip_transactions_table.php
│   │   └── create_gas_fees_table.php
│   └── factories/
│       └── RentChipTransactionFactory.php
└── tests/
    ├── Feature/
    │   └── RentChipTransferTest.php
    └── Unit/
        └── BlockchainServiceTest.php
```

### 3. PaymentGateway Module (`Modules/PaymentGateway`)

**Purpose**: Handle fiat to RentChip conversions

**Responsibilities**:
- Deposit: Fiat → RentChip conversion
- Withdraw: RentChip → Fiat conversion
- Integration with Stripe, PayPal, etc.
- Price oracle integration for conversion rates
- Transaction fee management
- Refund handling

**Key Components**:
```
Modules/PaymentGateway/
├── app/
│   ├── Models/
│   │   ├── FiatTransaction.php      # Fiat deposits/withdrawals
│   │   ├── ConversionRate.php       # Exchange rates
│   │   └── PaymentMethod.php        # User payment methods
│   ├── Services/
│   │   ├── DepositService.php       # Handle deposits
│   │   ├── WithdrawalService.php    # Handle withdrawals
│   │   ├── StripeService.php        # Stripe integration
│   │   ├── PayPalService.php        # PayPal integration
│   │   ├── PriceOracleService.php   # Exchange rate fetching
│   │   └── FeeCalculationService.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DepositController.php
│   │   │   ├── WithdrawalController.php
│   │   │   └── Api/PaymentGatewayApiController.php
│   │   └── Requests/
│   │       ├── DepositRequest.php
│   │       └── WithdrawalRequest.php
│   ├── Jobs/
│   │   ├── ProcessDepositJob.php
│   │   ├── ProcessWithdrawalJob.php
│   │   ├── PurchaseRentChipJob.php
│   │   └── UpdateExchangeRateJob.php
│   └── Enum/
│       ├── PaymentGatewayEnum.php
│       └── FiatTransactionStatusEnum.php
├── config/
│   └── payment-gateway.php
├── database/
│   ├── migrations/
│   │   ├── create_fiat_transactions_table.php
│   │   ├── create_conversion_rates_table.php
│   │   └── create_payment_methods_table.php
│   └── factories/
│       └── FiatTransactionFactory.php
└── tests/
    ├── Feature/
    │   ├── DepositFlowTest.php
    │   └── WithdrawalFlowTest.php
    └── Unit/
        └── PriceOracleServiceTest.php
```

### 4. RentPayment Module (`Modules/RentPayment`)

**Purpose**: Manage rent payments between tenants and landlords

**Responsibilities**:
- Schedule recurring rent payments
- Process rent payments in RentChip
- Handle payment reminders
- Late payment fees
- Payment history and receipts
- Escrow functionality (optional)

**Key Components**:
```
Modules/RentPayment/
├── app/
│   ├── Models/
│   │   ├── RentPayment.php          # Rent payment records
│   │   ├── PaymentSchedule.php      # Recurring payments
│   │   ├── RentAgreement.php        # Lease agreements
│   │   └── LateFee.php              # Late payment tracking
│   ├── Services/
│   │   ├── RentPaymentService.php   # Main payment logic
│   │   ├── ScheduleService.php      # Auto-payment scheduling
│   │   ├── ReceiptService.php       # Generate receipts
│   │   └── EscrowService.php        # Escrow management
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── RentPaymentController.php
│   │   │   └── Api/RentPaymentApiController.php
│   │   └── Requests/
│   │       ├── PayRentRequest.php
│   │       └── CreatePaymentScheduleRequest.php
│   ├── Jobs/
│   │   ├── ProcessRentPaymentJob.php
│   │   ├── SendPaymentReminderJob.php
│   │   └── ApplyLateFeeJob.php
│   ├── Mail/
│   │   ├── RentPaymentReceiptMail.php
│   │   └── PaymentReminderMail.php
│   └── Enum/
│       └── RentPaymentStatusEnum.php
├── database/
│   ├── migrations/
│   │   ├── create_rent_payments_table.php
│   │   ├── create_payment_schedules_table.php
│   │   ├── create_rent_agreements_table.php
│   │   └── create_late_fees_table.php
│   └── factories/
│       ├── RentPaymentFactory.php
│       └── PaymentScheduleFactory.php
└── tests/
    ├── Feature/
    │   ├── RentPaymentFlowTest.php
    │   └── ScheduledPaymentTest.php
    └── Unit/
        └── RentPaymentServiceTest.php
```

### 5. Update Existing User Module

**Additions to** `Modules/User`:
- Link users to wallets
- KYC/AML verification
- User role-specific wallet features
- Transaction limits based on verification level

---

## Database Schema

### Core Tables

#### 1. wallets
```php
Schema::create('wallets', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('address')->unique(); // Polygon address
    $table->text('encrypted_private_key'); // AES-256 encrypted
    $table->string('public_key');
    $table->decimal('balance', 36, 18)->default(0); // RentChip balance
    $table->decimal('balance_fiat_equivalent', 20, 2)->default(0); // USD equivalent
    $table->enum('status', ['active', 'frozen', 'suspended'])->default('active');
    $table->enum('type', ['custodial', 'non_custodial'])->default('custodial');
    $table->boolean('is_primary')->default(true);
    $table->timestamp('last_sync_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['user_id', 'is_primary']);
    $table->index('address');
});
```

#### 2. wallet_transactions
```php
Schema::create('wallet_transactions', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
    $table->foreignId('related_wallet_id')->nullable()->constrained('wallets');
    $table->string('transaction_hash')->nullable()->unique();
    $table->enum('type', ['deposit', 'withdrawal', 'transfer_in', 'transfer_out', 'fee', 'rent_payment', 'service_payment']);
    $table->decimal('amount', 36, 18);
    $table->decimal('fee', 36, 18)->default(0);
    $table->decimal('amount_fiat', 20, 2)->nullable();
    $table->string('currency', 10)->default('RENTCHIP');
    $table->enum('status', ['pending', 'processing', 'confirmed', 'failed', 'cancelled'])->default('pending');
    $table->text('description')->nullable();
    $table->json('metadata')->nullable(); // Additional info
    $table->integer('confirmations')->default(0);
    $table->timestamp('confirmed_at')->nullable();
    $table->timestamps();
    
    $table->index(['wallet_id', 'created_at']);
    $table->index('transaction_hash');
    $table->index(['type', 'status']);
});
```

#### 3. rentchip_transactions
```php
Schema::create('rentchip_transactions', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('transaction_hash')->unique();
    $table->string('from_address');
    $table->string('to_address');
    $table->decimal('amount', 36, 18);
    $table->decimal('gas_price', 36, 18);
    $table->decimal('gas_used', 36, 18)->nullable();
    $table->decimal('gas_fee', 36, 18)->nullable();
    $table->integer('block_number')->nullable();
    $table->enum('status', ['pending', 'mined', 'confirmed', 'failed'])->default('pending');
    $table->integer('confirmations')->default(0);
    $table->text('error_message')->nullable();
    $table->json('receipt')->nullable(); // Full transaction receipt
    $table->morphs('transactable'); // Links to rent_payments, etc.
    $table->timestamps();
    
    $table->index('transaction_hash');
    $table->index(['from_address', 'to_address']);
    $table->index('status');
});
```

#### 4. fiat_transactions
```php
Schema::create('fiat_transactions', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['deposit', 'withdrawal']);
    $table->enum('gateway', ['stripe', 'paypal', 'bank_transfer']);
    $table->string('gateway_transaction_id')->nullable();
    $table->decimal('amount_fiat', 20, 2);
    $table->string('currency', 3)->default('USD'); // USD, EUR, etc.
    $table->decimal('amount_rentchip', 36, 18);
    $table->decimal('exchange_rate', 20, 8); // Rate at transaction time
    $table->decimal('platform_fee', 20, 2)->default(0);
    $table->decimal('gateway_fee', 20, 2)->default(0);
    $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
    $table->json('payment_method')->nullable(); // Card info, bank details
    $table->json('gateway_response')->nullable();
    $table->text('notes')->nullable();
    $table->timestamp('processed_at')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'type']);
    $table->index('gateway_transaction_id');
    $table->index(['status', 'created_at']);
});
```

#### 5. conversion_rates
```php
Schema::create('conversion_rates', function (Blueprint $table) {
    $table->id();
    $table->string('from_currency', 10); // RENTCHIP
    $table->string('to_currency', 3); // USD, EUR
    $table->decimal('rate', 20, 8);
    $table->enum('source', ['manual', 'oracle', 'exchange_api']);
    $table->timestamp('valid_from');
    $table->timestamp('valid_until')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->index(['from_currency', 'to_currency', 'is_active']);
    $table->index('valid_from');
});
```

#### 6. rent_payments
```php
Schema::create('rent_payments', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('property_id')->constrained()->cascadeOnDelete();
    $table->foreignId('rent_agreement_id')->nullable()->constrained();
    $table->foreignId('wallet_transaction_id')->nullable()->constrained();
    $table->foreignId('rentchip_transaction_id')->nullable()->constrained();
    $table->string('payment_reference')->unique();
    $table->decimal('amount', 36, 18); // RentChip amount
    $table->decimal('amount_fiat', 20, 2); // USD equivalent
    $table->date('due_date');
    $table->date('paid_date')->nullable();
    $table->enum('status', ['scheduled', 'pending', 'processing', 'paid', 'failed', 'late', 'cancelled'])->default('scheduled');
    $table->enum('payment_type', ['rent', 'bond', 'late_fee', 'other']);
    $table->decimal('late_fee', 36, 18)->default(0);
    $table->boolean('is_recurring')->default(false);
    $table->string('receipt_url')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['tenant_id', 'status']);
    $table->index(['landlord_id', 'paid_date']);
    $table->index('due_date');
    $table->index('payment_reference');
});
```

#### 7. payment_schedules
```php
Schema::create('payment_schedules', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('property_id')->constrained()->cascadeOnDelete();
    $table->foreignId('rent_agreement_id')->constrained();
    $table->decimal('amount', 36, 18); // RentChip amount
    $table->enum('frequency', ['weekly', 'fortnightly', 'monthly'])->default('monthly');
    $table->integer('day_of_period'); // 1-31 for monthly, 1-7 for weekly
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->date('next_payment_date');
    $table->boolean('auto_pay_enabled')->default(false);
    $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
    $table->timestamps();
    
    $table->index(['tenant_id', 'status']);
    $table->index('next_payment_date');
});
```

#### 8. rent_agreements
```php
Schema::create('rent_agreements', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('property_id')->constrained()->cascadeOnDelete();
    $table->foreignId('agent_id')->nullable()->constrained('users');
    $table->string('agreement_number')->unique();
    $table->decimal('rent_amount', 36, 18); // In RentChip
    $table->decimal('bond_amount', 36, 18); // In RentChip
    $table->enum('payment_frequency', ['weekly', 'fortnightly', 'monthly']);
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->enum('status', ['draft', 'active', 'expired', 'terminated'])->default('draft');
    $table->string('signed_document_url')->nullable();
    $table->json('terms')->nullable(); // Additional terms
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['tenant_id', 'status']);
    $table->index(['landlord_id', 'status']);
    $table->index('property_id');
});
```

#### 9. gas_fees
```php
Schema::create('gas_fees', function (Blueprint $table) {
    $table->id();
    $table->decimal('gas_price_gwei', 20, 9); // Current gas price
    $table->decimal('estimated_transaction_cost', 20, 8); // In MATIC
    $table->decimal('estimated_transaction_cost_usd', 10, 2);
    $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
    $table->timestamp('recorded_at');
    $table->timestamps();
    
    $table->index('recorded_at');
});
```

### Relationship Summary

```
User
├── hasMany(Wallet)
├── hasMany(RentPayment) as tenant
├── hasMany(RentPayment) as landlord
├── hasMany(RentAgreement) as tenant
├── hasMany(RentAgreement) as landlord
└── hasMany(FiatTransaction)

Wallet
├── belongsTo(User)
├── hasMany(WalletTransaction)
└── hasMany(FiatTransaction)

RentPayment
├── belongsTo(User) as tenant
├── belongsTo(User) as landlord
├── belongsTo(Property)
├── belongsTo(RentAgreement)
├── belongsTo(WalletTransaction)
└── belongsTo(RentChipTransaction)

RentAgreement
├── belongsTo(User) as tenant
├── belongsTo(User) as landlord
├── belongsTo(Property)
├── hasMany(RentPayment)
└── hasMany(PaymentSchedule)
```

---

## Blockchain Integration

### Smart Contract Setup

#### RentChip ERC-20 Token Contract
```solidity
// Simplified contract interface
contract RentChip {
    string public name = "RentChip";
    string public symbol = "RCHIP";
    uint8 public decimals = 18;
    
    function transfer(address to, uint256 amount) external returns (bool);
    function balanceOf(address account) external view returns (uint256);
    function approve(address spender, uint256 amount) external returns (bool);
    function transferFrom(address from, address to, uint256 amount) external returns (bool);
}
```

### Polygon Network Configuration

**Config File**: `config/blockchain.php`
```php
return [
    'polygon' => [
        'rpc_url' => env('POLYGON_RPC_URL', 'https://polygon-rpc.com'),
        'chain_id' => env('POLYGON_CHAIN_ID', 137), // Mainnet: 137, Mumbai Testnet: 80001
        'explorer_url' => env('POLYGON_EXPLORER_URL', 'https://polygonscan.com'),
    ],
    
    'rentchip' => [
        'contract_address' => env('RENTCHIP_CONTRACT_ADDRESS'),
        'contract_abi' => json_decode(file_get_contents(storage_path('contracts/RentChip.json')), true),
        'decimals' => 18,
    ],
    
    'gas' => [
        'default_gas_limit' => env('GAS_LIMIT', 100000),
        'max_gas_price_gwei' => env('MAX_GAS_PRICE_GWEI', 100),
        'priority_fee_multiplier' => env('PRIORITY_FEE_MULTIPLIER', 1.2),
    ],
    
    'confirmations' => [
        'required' => env('REQUIRED_CONFIRMATIONS', 12),
        'fast' => 3,
        'standard' => 12,
        'secure' => 24,
    ],
    
    'hot_wallet' => [
        'address' => env('HOT_WALLET_ADDRESS'),
        'encrypted_private_key' => env('HOT_WALLET_PRIVATE_KEY'),
    ],
];
```

### Web3 Service Implementation

**File**: `app/Services/Web3Service.php`
```php
<?php

namespace App\Services;

use Web3\Web3;
use Web3\Contract;
use Web3\Utils;

class Web3Service
{
    protected Web3 $web3;
    protected Contract $rentChipContract;
    
    public function __construct()
    {
        $this->web3 = new Web3(config('blockchain.polygon.rpc_url'));
        $this->initializeRentChipContract();
    }
    
    protected function initializeRentChipContract(): void
    {
        $this->rentChipContract = new Contract(
            config('blockchain.polygon.rpc_url'),
            config('blockchain.rentchip.contract_abi')
        );
    }
    
    public function getBalance(string $address): string
    {
        // Implementation
    }
    
    public function transfer(string $from, string $to, string $amount, string $privateKey): string
    {
        // Implementation
    }
    
    public function estimateGas(array $transaction): int
    {
        // Implementation
    }
    
    public function getTransactionReceipt(string $txHash): ?array
    {
        // Implementation
    }
}
```

### Custodial Wallet Security

**Key Management Strategy**:
1. **Master Key**: Stored in HSM (Hardware Security Module) or secure vault
2. **User Private Keys**: Encrypted with AES-256 using master key
3. **Hot Wallet**: Small amount for immediate transactions
4. **Cold Storage**: Majority of funds stored offline

**Encryption Service**: `app/Services/EncryptionService.php`
```php
<?php

namespace App\Services;

class EncryptionService
{
    protected string $masterKey;
    
    public function __construct()
    {
        $this->masterKey = config('app.master_encryption_key');
    }
    
    public function encryptPrivateKey(string $privateKey): string
    {
        return openssl_encrypt(
            $privateKey,
            'AES-256-CBC',
            $this->masterKey,
            0,
            $this->getIV()
        );
    }
    
    public function decryptPrivateKey(string $encryptedKey): string
    {
        return openssl_decrypt(
            $encryptedKey,
            'AES-256-CBC',
            $this->masterKey,
            0,
            $this->getIV()
        );
    }
    
    protected function getIV(): string
    {
        return substr(hash('sha256', $this->masterKey), 0, 16);
    }
}
```

---

## Payment Flows

### Flow 1: User Onboarding & Wallet Creation

```
1. User registers → User Module
2. Create user account → Database
3. Trigger wallet creation → Wallet Module
4. Generate wallet address & keys → BlockchainService
5. Encrypt private key → EncryptionService
6. Store wallet → wallets table
7. Send welcome email with wallet address
```

**Code Example**:
```php
// In UserObserver@created
public function created(User $user): void
{
    CreateWalletJob::dispatch($user);
}

// In CreateWalletJob
public function handle(WalletService $walletService): void
{
    $walletService->createWalletForUser($this->user);
}
```

### Flow 2: Deposit (Fiat → RentChip)

```
1. User initiates deposit → PaymentGateway Module
2. User enters amount & payment method
3. Process payment via Stripe/PayPal → StripeService
4. On success, fetch current exchange rate → PriceOracleService
5. Calculate RentChip amount to credit
6. Create fiat_transaction record
7. Queue RentChip purchase → PurchaseRentChipJob
8. Transfer RentChip from hot wallet to user wallet → RentChipService
9. Update user wallet balance
10. Send confirmation email & notification
```

**Code Example**:
```php
// In DepositController
public function process(DepositRequest $request, DepositService $depositService)
{
    $deposit = $depositService->processDeposit(
        user: auth()->user(),
        amount: $request->amount,
        currency: $request->currency,
        gateway: $request->gateway,
        paymentMethod: $request->payment_method
    );
    
    return response()->json([
        'success' => true,
        'deposit' => $deposit,
        'message' => 'Deposit initiated successfully'
    ]);
}
```

### Flow 3: Rent Payment (Tenant → Landlord)

```
1. Scheduled payment due date arrives
2. Job checks payment_schedules → ProcessRentPaymentJob
3. Check tenant wallet balance → WalletService
4. If insufficient, send notification & mark as failed
5. If sufficient:
   a. Create rent_payment record (status: pending)
   b. Initiate blockchain transfer → RentChipService
   c. Submit transaction to Polygon network
   d. Store transaction hash → rentchip_transactions
   e. Update rent_payment (status: processing)
6. Monitor transaction confirmation → ConfirmTransactionJob
7. After required confirmations:
   a. Update rent_payment (status: paid)
   b. Update wallet balances
   c. Generate receipt → ReceiptService
   d. Send notifications to tenant & landlord
   e. If agent assigned, send notification to agent
```

**Code Example**:
```php
// In PaymentSchedule model
protected static function boot()
{
    parent::boot();
    
    static::created(function ($schedule) {
        ProcessRentPaymentJob::dispatch($schedule)
            ->delay($schedule->next_payment_date);
    });
}

// In ProcessRentPaymentJob
public function handle(RentPaymentService $rentPaymentService): void
{
    $rentPaymentService->processScheduledPayment($this->schedule);
}
```

### Flow 4: Withdrawal (RentChip → Fiat)

```
1. User initiates withdrawal → PaymentGateway Module
2. User enters RentChip amount & bank details
3. Validate sufficient balance → WalletService
4. Fetch current exchange rate → PriceOracleService
5. Calculate fiat amount (minus fees)
6. Create fiat_transaction record (status: pending)
7. Lock RentChip in user wallet
8. Queue withdrawal processing → ProcessWithdrawalJob
9. Admin reviews withdrawal (optional, based on amount)
10. On approval:
    a. Transfer fiat via payment gateway
    b. Burn/transfer RentChip from user wallet
    c. Update fiat_transaction (status: completed)
    d. Send confirmation
```

### Flow 5: Service Vendor Payment

```
1. Tenant/Landlord books service → Service Module
2. Service completed & approved
3. Payment initiated to vendor
4. Transfer RentChip from payer to vendor wallet → RentChipService
5. Platform fee deducted (if applicable)
6. Create wallet_transaction records
7. Update balances
8. Send payment confirmation
```

---

## Security Considerations

### Critical Security Measures

#### 1. Private Key Management
- **Never store plain text private keys**
- Use AES-256 encryption with master key
- Master key stored in AWS KMS, HashiCorp Vault, or HSM
- Implement key rotation policy
- Use separate hot/cold wallet strategy

#### 2. Transaction Security
- Implement transaction limits per user tier
- Require 2FA for withdrawals above threshold
- Multi-signature for high-value transactions
- Rate limiting on transfer endpoints
- Nonce management to prevent replay attacks

#### 3. Smart Contract Security
- Audit RentChip contract before deployment
- Implement pausable functionality for emergencies
- Use OpenZeppelin libraries for standard functions
- Test on testnet extensively before mainnet

#### 4. API Security
- JWT authentication for API endpoints
- IP whitelisting for webhook endpoints
- Signature verification for payment gateway callbacks
- HTTPS only for all communications
- CORS configuration

#### 5. Monitoring & Alerts
- Real-time transaction monitoring
- Anomaly detection for unusual patterns
- Alert system for failed transactions
- Wallet balance alerts
- Gas price spike notifications

#### 6. Compliance
- KYC/AML verification integration
- Transaction limits before KYC
- Enhanced due diligence for large transactions
- Record keeping for regulatory compliance
- Privacy policy compliance (GDPR, etc.)

### Implementation Checklist

```php
// Middleware for transaction limits
class CheckTransactionLimit
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $amount = $request->amount;
        
        // Check if user is KYC verified
        if (!$user->is_kyc_verified && $amount > 1000) {
            return response()->json([
                'error' => 'KYC verification required for amounts over $1000'
            ], 403);
        }
        
        // Check daily limit
        $dailyTotal = $user->transactions()
            ->whereDate('created_at', today())
            ->sum('amount');
            
        if ($dailyTotal + $amount > $user->daily_limit) {
            return response()->json([
                'error' => 'Daily transaction limit exceeded'
            ], 403);
        }
        
        return $next($request);
    }
}
```

---

## Implementation Phases

### Phase 1: Foundation (Weeks 1-4)

**Objective**: Set up core infrastructure

**Tasks**:
1. Deploy RentChip smart contract to Polygon Mumbai (testnet)
2. Create Wallet Module
   - Wallet model & migrations
   - WalletService for creation
   - Encryption service for key management
3. Create RentChip Module
   - BlockchainService for Polygon interaction
   - SmartContractService for RentChip operations
4. Set up Web3 integration
5. Create basic wallet UI
   - View wallet address
   - View balance
   - Transaction history

**Deliverables**:
- Working wallet creation
- Balance checking from blockchain
- Test transaction capability

### Phase 2: Payment Gateway Integration (Weeks 5-7)

**Objective**: Enable fiat deposits and withdrawals

**Tasks**:
1. Create PaymentGateway Module
2. Integrate Stripe for deposits/withdrawals
3. Implement PriceOracleService
   - Fetch exchange rates
   - Update rates periodically
4. Build deposit flow
   - UI for deposit
   - Payment processing
   - RentChip crediting
5. Build withdrawal flow
   - UI for withdrawal
   - Withdrawal processing
   - Fiat payout
6. Implement transaction fees

**Deliverables**:
- Working deposit system
- Working withdrawal system
- Exchange rate management

### Phase 3: Rent Payment System (Weeks 8-11)

**Objective**: Core rent payment functionality

**Tasks**:
1. Create RentPayment Module
2. Build RentAgreement system
   - Agreement creation
   - Terms management
   - Digital signatures
3. Implement PaymentSchedule
   - Recurring payment setup
   - Auto-pay functionality
4. Build rent payment processing
   - One-time payments
   - Scheduled payments
   - Late fee calculation
5. Receipt generation
6. Payment reminders

**Deliverables**:
- Working rent payment system
- Automated recurring payments
- Receipt generation

### Phase 4: Multi-Stakeholder Features (Weeks 12-14)

**Objective**: Implement agent and service vendor functionality

**Tasks**:
1. Agent payment distribution
   - Commission splitting
   - Agent wallet management
2. Service vendor payments
   - Service booking payment flow
   - Vendor wallet management
3. Escrow functionality (optional)
4. Payment splits for multiple parties

**Deliverables**:
- Agent commission system
- Service vendor payment system

### Phase 5: Security & Compliance (Weeks 15-16)

**Objective**: Harden security and implement compliance

**Tasks**:
1. KYC/AML integration
2. Transaction monitoring system
3. Fraud detection
4. Security audit
5. Penetration testing
6. Cold wallet setup for reserves

**Deliverables**:
- KYC system
- Monitoring dashboard
- Security audit report

### Phase 6: Testing & Deployment (Weeks 17-18)

**Objective**: Comprehensive testing and mainnet deployment

**Tasks**:
1. Deploy RentChip to Polygon mainnet
2. Comprehensive integration testing
3. Load testing
4. User acceptance testing
5. Documentation
6. Training materials

**Deliverables**:
- Production-ready system
- Documentation
- Training materials

---

## API Design

### REST API Endpoints

#### Wallet Endpoints

```
GET    /api/v1/wallet                      # Get user's wallet
POST   /api/v1/wallet                      # Create wallet
GET    /api/v1/wallet/balance              # Get balance
GET    /api/v1/wallet/transactions         # Get transaction history
POST   /api/v1/wallet/transfer             # Transfer RentChip
```

#### Payment Gateway Endpoints

```
POST   /api/v1/payment/deposit             # Initiate deposit
POST   /api/v1/payment/withdraw            # Initiate withdrawal
GET    /api/v1/payment/conversion-rate     # Get current rate
GET    /api/v1/payment/methods             # Get saved payment methods
POST   /api/v1/payment/methods             # Add payment method
DELETE /api/v1/payment/methods/{id}        # Remove payment method
GET    /api/v1/payment/transactions        # Get fiat transactions
```

#### Rent Payment Endpoints

```
POST   /api/v1/rent/pay                    # Pay rent
GET    /api/v1/rent/payments               # Get rent payments
GET    /api/v1/rent/payments/{id}          # Get specific payment
POST   /api/v1/rent/schedule               # Create payment schedule
GET    /api/v1/rent/schedule               # Get payment schedules
PUT    /api/v1/rent/schedule/{id}          # Update schedule
DELETE /api/v1/rent/schedule/{id}          # Cancel schedule
GET    /api/v1/rent/agreements             # Get rent agreements
POST   /api/v1/rent/agreements             # Create agreement
GET    /api/v1/rent/receipts/{id}          # Download receipt
```

#### Blockchain Endpoints

```
GET    /api/v1/blockchain/transaction/{hash}   # Get transaction details
GET    /api/v1/blockchain/gas-price            # Get current gas price
POST   /api/v1/blockchain/estimate-fee         # Estimate transaction fee
```

### API Response Format

**Success Response**:
```json
{
    "success": true,
    "data": {
        "id": 123,
        "amount": "550.000000000000000000",
        "status": "confirmed"
    },
    "meta": {
        "timestamp": "2025-11-23T10:30:00Z",
        "request_id": "req_abc123"
    }
}
```

**Error Response**:
```json
{
    "success": false,
    "error": {
        "code": "INSUFFICIENT_BALANCE",
        "message": "Insufficient RentChip balance",
        "details": {
            "required": "550.00",
            "available": "450.00"
        }
    },
    "meta": {
        "timestamp": "2025-11-23T10:30:00Z",
        "request_id": "req_abc123"
    }
}
```

### Webhook Endpoints

```
POST   /webhooks/stripe                    # Stripe webhooks
POST   /webhooks/paypal                    # PayPal webhooks
POST   /webhooks/blockchain                # Blockchain event notifications
```

---

## Testing Strategy

### Unit Tests

**Test Coverage Requirements**: Minimum 80%

**Key Areas**:
1. **Wallet Service Tests**
   - Wallet creation
   - Balance calculations
   - Transaction recording

2. **Blockchain Service Tests**
   - Mock blockchain interactions
   - Gas estimation
   - Transaction signing

3. **Payment Service Tests**
   - Deposit processing
   - Withdrawal processing
   - Fee calculations

4. **Encryption Service Tests**
   - Key encryption/decryption
   - Key validation

**Example Test**:
```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Modules\Wallet\Services\WalletService;
use App\Models\User;

class WalletServiceTest extends TestCase
{
    protected WalletService $walletService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->walletService = app(WalletService::class);
    }
    
    public function test_can_create_wallet_for_user(): void
    {
        $user = User::factory()->create();
        
        $wallet = $this->walletService->createWalletForUser($user);
        
        $this->assertNotNull($wallet);
        $this->assertNotNull($wallet->address);
        $this->assertNotNull($wallet->encrypted_private_key);
        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertEquals('active', $wallet->status);
    }
    
    public function test_wallet_address_is_unique(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $wallet1 = $this->walletService->createWalletForUser($user1);
        $wallet2 = $this->walletService->createWalletForUser($user2);
        
        $this->assertNotEquals($wallet1->address, $wallet2->address);
    }
}
```

### Feature Tests

**Key Scenarios**:
1. **Complete Deposit Flow**
   - User deposits $100 via Stripe
   - Receives equivalent RentChip
   - Balance updated correctly

2. **Complete Rent Payment Flow**
   - Tenant pays monthly rent
   - Transaction confirmed on blockchain
   - Landlord receives payment
   - Receipt generated

3. **Withdrawal Flow**
   - User withdraws RentChip to fiat
   - Balance deducted
   - Fiat transferred to bank

**Example Test**:
```php
<?php

namespace Tests\Feature\RentPayment;

use Tests\TestCase;
use App\Models\User;
use Modules\Wallet\Models\Wallet;
use Modules\RentPayment\Models\RentPayment;

class RentPaymentFlowTest extends TestCase
{
    public function test_tenant_can_pay_rent_to_landlord(): void
    {
        // Arrange
        $tenant = User::factory()->tenant()->create();
        $landlord = User::factory()->landlord()->create();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        
        $tenantWallet = Wallet::factory()->create([
            'user_id' => $tenant->id,
            'balance' => '1000.000000000000000000' // Sufficient balance
        ]);
        
        $rentAmount = '550.000000000000000000';
        
        // Act
        $response = $this->actingAs($tenant)
            ->postJson('/api/v1/rent/pay', [
                'property_id' => $property->id,
                'amount' => $rentAmount
            ]);
        
        // Assert
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('rent_payments', [
            'tenant_id' => $tenant->id,
            'landlord_id' => $landlord->id,
            'property_id' => $property->id,
            'amount' => $rentAmount
        ]);
        
        // Verify wallet balance deducted (after blockchain confirmation)
        // This would be tested with job queue or mocked blockchain
    }
    
    public function test_rent_payment_fails_with_insufficient_balance(): void
    {
        $tenant = User::factory()->tenant()->create();
        $property = Property::factory()->create();
        
        Wallet::factory()->create([
            'user_id' => $tenant->id,
            'balance' => '100.000000000000000000' // Insufficient
        ]);
        
        $response = $this->actingAs($tenant)
            ->postJson('/api/v1/rent/pay', [
                'property_id' => $property->id,
                'amount' => '550.000000000000000000'
            ]);
        
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'INSUFFICIENT_BALANCE'
            ]
        ]);
    }
}
```

### Integration Tests

**Areas to Test**:
1. Stripe integration (use Stripe test mode)
2. Blockchain interaction (use testnet)
3. Email notifications
4. Webhook processing

### Performance Tests

**Load Testing Scenarios**:
1. 1000 concurrent users viewing wallets
2. 100 simultaneous rent payments
3. 50 deposits processing concurrently

**Tools**: Apache JMeter, Laravel Dusk for browser testing

---

## AI Implementation Guidelines

### Overview for AI Developers

This section provides specific guidelines for AI agents implementing the RentChip payment system. Follow these principles to ensure consistent, secure, and maintainable code.

### Code Generation Principles

#### 1. Follow Laravel Conventions

**Always**:
- Use Eloquent ORM over raw queries
- Implement Form Request validation
- Use Laravel events and listeners
- Follow PSR-12 coding standards
- Use PHP 8.4 features (constructor property promotion, named arguments, etc.)

**Example**:
```php
// ✅ GOOD: Using Eloquent with Form Request
public function store(CreateWalletRequest $request, WalletService $walletService)
{
    $wallet = $walletService->createWalletForUser(
        user: auth()->user(),
        type: $request->type
    );
    
    return response()->json([
        'success' => true,
        'data' => $wallet
    ]);
}

// ❌ BAD: Raw query without validation
public function store(Request $request)
{
    $wallet = DB::table('wallets')->insert([
        'user_id' => $request->user_id,
        'address' => $request->address
    ]);
    
    return $wallet;
}
```

#### 2. Security First

**Critical Rules**:
- Never log or expose private keys
- Always encrypt sensitive data
- Use database transactions for financial operations
- Implement proper authorization checks
- Validate all user input

**Example**:
```php
// ✅ GOOD: Proper security measures
public function transfer(TransferRequest $request, RentChipService $rentChipService)
{
    // Authorization check
    $this->authorize('transfer', $request->user()->wallet);
    
    // Database transaction
    DB::beginTransaction();
    try {
        $transaction = $rentChipService->transfer(
            from: $request->user()->wallet,
            to: $request->recipient_address,
            amount: $request->amount
        );
        
        DB::commit();
        
        return response()->json(['success' => true, 'data' => $transaction]);
    } catch (\Exception $e) {
        DB::rollBack();
        
        // Log error without exposing sensitive data
        Log::error('Transfer failed', [
            'user_id' => $request->user()->id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json(['success' => false, 'error' => 'Transfer failed'], 500);
    }
}
```

#### 3. Use Jobs for Blockchain Operations

**Always queue blockchain operations**:
- Wallet creation
- Transaction processing
- Balance synchronization
- Transaction confirmation monitoring

**Example**:
```php
// ✅ GOOD: Queued blockchain operation
public function processRentPayment(RentPayment $rentPayment)
{
    ProcessRentPaymentJob::dispatch($rentPayment)
        ->onQueue('blockchain')
        ->delay(now()->addSeconds(5));
}

// Job implementation
class ProcessRentPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public RentPayment $rentPayment
    ) {}
    
    public function handle(RentChipService $rentChipService): void
    {
        $transaction = $rentChipService->transferForRentPayment($this->rentPayment);
        
        // Queue confirmation monitoring
        ConfirmTransactionJob::dispatch($transaction)
            ->delay(now()->addMinutes(2));
    }
    
    public function failed(\Throwable $exception): void
    {
        $this->rentPayment->update(['status' => 'failed']);
        
        // Notify tenant and landlord
        SendRentPaymentFailedNotification::dispatch($this->rentPayment);
    }
}
```

#### 4. Implement Proper Error Handling

**Use exceptions appropriately**:
```php
// Create custom exceptions
namespace Modules\Wallet\Exceptions;

class InsufficientBalanceException extends \Exception
{
    public function __construct(
        public readonly string $address,
        public readonly string $required,
        public readonly string $available
    ) {
        parent::__construct("Insufficient balance. Required: {$required}, Available: {$available}");
    }
}

// Use in service
public function transfer(Wallet $from, string $to, string $amount): Transaction
{
    if (bccomp($from->balance, $amount, 18) < 0) {
        throw new InsufficientBalanceException(
            address: $from->address,
            required: $amount,
            available: $from->balance
        );
    }
    
    // Process transfer
}

// Handle in controller
public function transfer(TransferRequest $request)
{
    try {
        $transaction = $this->walletService->transfer(...);
        return response()->json(['success' => true, 'data' => $transaction]);
    } catch (InsufficientBalanceException $e) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'INSUFFICIENT_BALANCE',
                'message' => $e->getMessage(),
                'details' => [
                    'required' => $e->required,
                    'available' => $e->available
                ]
            ]
        ], 422);
    }
}
```

#### 5. Database Best Practices

**Critical Guidelines**:
- Use migrations for all schema changes
- Always add indexes for foreign keys and frequently queried columns
- Use `decimal` type for financial amounts
- Implement soft deletes for financial records
- Use database transactions for multi-step operations

**Example Migration**:
```php
// ✅ GOOD: Proper migration with indexes and constraints
public function up(): void
{
    Schema::create('rent_payments', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        
        // Foreign keys with indexes
        $table->foreignId('tenant_id')
            ->constrained('users')
            ->cascadeOnDelete();
        $table->foreignId('landlord_id')
            ->constrained('users')
            ->cascadeOnDelete();
        
        // Financial amounts as decimals
        $table->decimal('amount', 36, 18);
        $table->decimal('amount_fiat', 20, 2);
        
        // Proper enum for status
        $table->enum('status', ['scheduled', 'pending', 'processing', 'paid', 'failed', 'late'])
            ->default('scheduled');
        
        $table->timestamps();
        $table->softDeletes();
        
        // Composite indexes for common queries
        $table->index(['tenant_id', 'status']);
        $table->index(['landlord_id', 'paid_date']);
        $table->index('due_date');
    });
}
```

#### 6. Testing Requirements

**Every feature must have tests**:
```php
// Feature test example
public function test_deposit_flow_completes_successfully(): void
{
    // Arrange
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);
    
    // Mock Stripe
    $this->mock(StripeService::class, function ($mock) {
        $mock->shouldReceive('createPaymentIntent')
            ->once()
            ->andReturn(['id' => 'pi_test123', 'status' => 'succeeded']);
    });
    
    // Mock blockchain
    Queue::fake();
    
    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/v1/payment/deposit', [
            'amount' => 100,
            'currency' => 'USD',
            'payment_method' => 'pm_test123'
        ]);
    
    // Assert
    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    
    Queue::assertPushed(PurchaseRentChipJob::class);
    
    $this->assertDatabaseHas('fiat_transactions', [
        'user_id' => $user->id,
        'type' => 'deposit',
        'amount_fiat' => 100,
        'status' => 'processing'
    ]);
}
```

### Module Creation Checklist

When creating a new module, follow this checklist:

- [ ] Create module using `php artisan module:make ModuleName`
- [ ] Define models with proper relationships
- [ ] Create migrations with indexes and constraints
- [ ] Implement factories for all models
- [ ] Create Form Request classes for validation
- [ ] Implement service classes for business logic
- [ ] Create jobs for async operations
- [ ] Add observers for model events (if needed)
- [ ] Implement API controllers with proper responses
- [ ] Add routes in module's routes file
- [ ] Create Enums for status fields
- [ ] Write unit tests for services
- [ ] Write feature tests for controllers
- [ ] Add documentation in module README

### Common Patterns

#### Pattern 1: Service Layer
```php
// Services contain business logic
class RentPaymentService
{
    public function __construct(
        protected WalletService $walletService,
        protected RentChipService $rentChipService,
        protected ReceiptService $receiptService
    ) {}
    
    public function processPayment(RentPayment $payment): RentPayment
    {
        // Validate
        $this->validatePayment($payment);
        
        // Update status
        $payment->update(['status' => 'processing']);
        
        // Process blockchain transaction
        $transaction = $this->rentChipService->transfer(
            from: $payment->tenant->wallet,
            to: $payment->landlord->wallet,
            amount: $payment->amount
        );
        
        // Link transaction
        $payment->update(['rentchip_transaction_id' => $transaction->id]);
        
        // Queue receipt generation
        GenerateReceiptJob::dispatch($payment);
        
        return $payment->fresh();
    }
}
```

#### Pattern 2: Repository Pattern (Optional for Complex Queries)
```php
class RentPaymentRepository
{
    public function __construct(
        protected RentPayment $model
    ) {}
    
    public function getOverduePayments(): Collection
    {
        return $this->model
            ->where('status', 'scheduled')
            ->where('due_date', '<', now())
            ->with(['tenant', 'landlord', 'property'])
            ->get();
    }
    
    public function getTenantPaymentHistory(User $tenant, ?int $limit = null): Collection
    {
        return $this->model
            ->where('tenant_id', $tenant->id)
            ->with(['property', 'landlord'])
            ->latest('paid_date')
            ->when($limit, fn($q) => $q->limit($limit))
            ->get();
    }
}
```

#### Pattern 3: Events and Listeners
```php
// Event
class RentPaymentCompleted
{
    public function __construct(
        public RentPayment $payment
    ) {}
}

// Listener
class SendRentPaymentNotifications
{
    public function handle(RentPaymentCompleted $event): void
    {
        // Notify tenant
        $event->payment->tenant->notify(
            new RentPaymentProcessedNotification($event->payment)
        );
        
        // Notify landlord
        $event->payment->landlord->notify(
            new RentReceivedNotification($event->payment)
        );
        
        // Notify agent if assigned
        if ($event->payment->property->agent) {
            $event->payment->property->agent->notify(
                new RentPaymentCompletedNotification($event->payment)
            );
        }
    }
}

// Register in EventServiceProvider
protected $listen = [
    RentPaymentCompleted::class => [
        SendRentPaymentNotifications::class,
        UpdatePaymentStatistics::class,
    ],
];
```

### Decimal Precision Handling

**Always use BC Math for financial calculations**:
```php
// ✅ GOOD: Using BC Math
$total = bcadd($amount, $fee, 18);
$remaining = bcsub($balance, $total, 18);

if (bccomp($remaining, '0', 18) >= 0) {
    // Sufficient balance
}

// ❌ BAD: Float arithmetic
$total = $amount + $fee; // Will lose precision
```

### Configuration Management

**Never use `env()` outside config files**:
```php
// ✅ GOOD: Use config()
$contractAddress = config('blockchain.rentchip.contract_address');

// ❌ BAD: Direct env() access
$contractAddress = env('RENTCHIP_CONTRACT_ADDRESS');
```

### API Versioning

**Always version APIs**:
```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('wallet')->group(function () {
            Route::get('/', [WalletController::class, 'show']);
            Route::post('/transfer', [WalletController::class, 'transfer']);
        });
    });
});
```

---

## Additional Considerations

### Gas Fee Management

**Strategy**:
1. Monitor Polygon gas prices in real-time
2. Implement dynamic gas pricing
3. Batch transactions when possible
4. Use gas tokens for optimization
5. Provide gas fee estimates to users before transactions

### Transaction Monitoring

**Implementation**:
```php
class TransactionMonitoringService
{
    public function monitorTransaction(RentChipTransaction $transaction): void
    {
        $receipt = $this->web3Service->getTransactionReceipt($transaction->transaction_hash);
        
        if ($receipt) {
            $transaction->update([
                'block_number' => $receipt['blockNumber'],
                'gas_used' => $receipt['gasUsed'],
                'status' => $receipt['status'] ? 'mined' : 'failed'
            ]);
            
            // Check confirmations
            $currentBlock = $this->web3Service->getBlockNumber();
            $confirmations = $currentBlock - $receipt['blockNumber'];
            
            if ($confirmations >= config('blockchain.confirmations.required')) {
                $transaction->update(['status' => 'confirmed']);
                event(new TransactionConfirmed($transaction));
            }
        }
    }
}
```

### Scalability Considerations

1. **Database**:
   - Partition large tables (transactions) by date
   - Use read replicas for reporting queries
   - Implement caching for frequently accessed data

2. **Blockchain**:
   - Use Alchemy or Infura for reliable RPC access
   - Implement connection pooling
   - Cache blockchain data where appropriate

3. **Queue Workers**:
   - Separate queues for different priorities
   - Scale workers based on queue depth
   - Implement job retry logic with exponential backoff

### Backup & Recovery

**Critical Data**:
1. User wallets and encrypted keys
2. Transaction history
3. Financial records

**Strategy**:
- Daily encrypted backups
- Cold storage for master encryption key
- Disaster recovery plan
- Regular restoration testing

---

## Deployment Checklist

### Pre-Deployment

- [ ] Smart contract audited
- [ ] Security penetration testing completed
- [ ] Load testing passed
- [ ] All tests passing (unit, feature, integration)
- [ ] Documentation completed
- [ ] Backup strategy implemented
- [ ] Monitoring configured
- [ ] Alert system tested

### Mainnet Deployment

- [ ] Deploy RentChip contract to Polygon mainnet
- [ ] Verify contract on Polygonscan
- [ ] Configure production RPC endpoints
- [ ] Set up hot wallet with initial funds
- [ ] Configure cold storage
- [ ] Enable monitoring and alerts
- [ ] Test with small transactions first

### Post-Deployment

- [ ] Monitor first 100 transactions closely
- [ ] Verify gas fees are reasonable
- [ ] Check transaction confirmation times
- [ ] Monitor error rates
- [ ] Collect user feedback
- [ ] Document any issues

---

## Support & Maintenance

### Monitoring Dashboard

**Key Metrics**:
- Total RentChip in circulation
- Number of active wallets
- Transaction volume (24h, 7d, 30d)
- Failed transaction rate
- Average gas fees
- Deposit/withdrawal volume
- Rent payment volume

### Maintenance Tasks

**Daily**:
- Check failed transactions
- Monitor gas prices
- Review error logs
- Check wallet balances

**Weekly**:
- Review transaction patterns
- Update exchange rates manually if needed
- Security log review

**Monthly**:
- Performance optimization
- Database cleanup
- Security audit
- Backup verification

---

## Conclusion

This implementation guide provides a comprehensive blueprint for integrating RentChip cryptocurrency payments into the Renter Pay platform. By following these guidelines, AI developers can build a secure, scalable, and maintainable payment system that serves all stakeholders effectively.

### Key Takeaways

1. **Modular Architecture**: Keep code organized in Laravel modules
2. **Security First**: Protect private keys and implement proper authorization
3. **Async Processing**: Use queues for all blockchain operations
4. **Testing**: Maintain high test coverage
5. **Monitoring**: Implement comprehensive monitoring and alerting
6. **Documentation**: Keep code well-documented and maintainable

For questions or clarifications, refer to the existing codebase patterns and Laravel documentation.

---

**Document Version**: 1.0  
**Last Updated**: November 23, 2025  
**Author**: AI Implementation Team  
**Status**: Ready for Implementation

