<?php

// routes/api.php

use App\Http\Controllers\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::post('/waitlist', WaitlistController::class);
