<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Controllers\Site\AuthController;
use Growfund\Controllers\Site\BackerController;
use Growfund\Controllers\Site\CheckoutController;
use Growfund\Controllers\Site\DonorController;
use Growfund\Controllers\Site\PaymentController;
use Growfund\Controllers\Site\GuestController;
use Growfund\Controllers\Site\WebhookController;
use Growfund\Middlewares\Site\BackerMiddleware;
use Growfund\Middlewares\Site\DonorMiddleware;
use Growfund\Middlewares\Site\GuestMiddleware;
use Growfund\SiteRouter;

// Authentication Routes
SiteRouter::get('auth/login/', [AuthController::class, 'show_login'])->middleware(GuestMiddleware::class)->name('auth.login.show');
SiteRouter::post('growfund/auth/login', [AuthController::class, 'login'])->middleware(GuestMiddleware::class)->name('auth.login')->with_nonce();
SiteRouter::get('auth/register/', [AuthController::class, 'show_register'])->middleware(GuestMiddleware::class)->name('auth.register.show');
SiteRouter::post('growfund/auth/register', [AuthController::class, 'register'])->middleware(GuestMiddleware::class)->name('auth.register')->with_nonce();
SiteRouter::get('auth/forgot-password/', [AuthController::class, 'show_forgot_password'])->middleware(GuestMiddleware::class)->name('auth.forgot-password.show');
SiteRouter::post('growfund/auth/forgot-password', [AuthController::class, 'forgot_password'])->middleware(GuestMiddleware::class)->name('auth.forgot-password')->with_nonce();
SiteRouter::get('auth/reset-password-link', [AuthController::class, 'show_reset_password_link'])->middleware(GuestMiddleware::class)->name('auth.reset-password-link');
SiteRouter::get('auth/reset-password/', [AuthController::class, 'show_reset_password'])->middleware(GuestMiddleware::class)->name('auth.reset-password.show');
SiteRouter::post('growfund/auth/reset-password', [AuthController::class, 'reset_password'])->middleware(GuestMiddleware::class)->name('auth.reset-password')->with_nonce();

// Dashboard Routes

SiteRouter::get('dashboard/backer', [BackerController::class, 'show'])
    ->middleware(BackerMiddleware::class)->name('dashboard.backer');
SiteRouter::get('dashboard/donor', [DonorController::class, 'show'])
    ->middleware(DonorMiddleware::class)->name('dashboard.donor');

// Checkout Routes
SiteRouter::get('campaign/checkout', [CheckoutController::class, 'show'])->name('checkout.index');
SiteRouter::post('campaign/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->with_nonce();

// Payment Routes
SiteRouter::get('payment/confirm', [PaymentController::class, 'confirm']);
SiteRouter::post('webhook/payment', [WebhookController::class, 'handle']);

// public routes
SiteRouter::get('public', [GuestController::class, 'show'])->name('public');

SiteRouter::register();
