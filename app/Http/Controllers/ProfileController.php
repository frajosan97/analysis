<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Edit Profile
    |--------------------------------------------------------------------------
    |
    | Displays the user profile edit form. Includes information about whether
    | the user's email needs verification and any session status messages.
    |
    | @param Request $request The incoming HTTP request
    | @return Response Inertia response with profile edit page data
    |
    */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $this->needsEmailVerification($request->user()),
            'status' => session('status'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Profile
    |--------------------------------------------------------------------------
    |
    | Handles updating the user's profile information. If the email address is
    | changed, marks the email as unverified. Validates input using the
    | ProfileUpdateRequest form request class.
    |
    | @param ProfileUpdateRequest $request The validated form request
    | @return RedirectResponse Redirects back to profile edit page
    |
    */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $this->updateUserProfile($user, $request->validated());

        return Redirect::route('profile.edit');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Account
    |--------------------------------------------------------------------------
    |
    | Handles user account deletion. Requires current password confirmation,
    | logs the user out, deletes the account, and invalidates the session.
    |
    | @param Request $request The incoming HTTP request
    | @return RedirectResponse Redirects to home page after deletion
    |
    */
    public function destroy(Request $request): RedirectResponse
    {
        $this->validateAccountDeletion($request);
        $user = $request->user();

        $this->logoutAndDeleteUser($user, $request);

        return Redirect::to('/');
    }

    /*
    |--------------------------------------------------------------------------
    | Check Email Verification Requirement
    |--------------------------------------------------------------------------
    |
    | Determines if the given user needs email verification.
    |
    | @param mixed $user The user model
    | @return bool True if email verification is required
    |
    */
    private function needsEmailVerification($user): bool
    {
        return $user instanceof MustVerifyEmail;
    }

    /*
    |--------------------------------------------------------------------------
    | Update User Profile Data
    |--------------------------------------------------------------------------
    |
    | Updates user profile information and handles email verification status
    | if the email address was changed.
    |
    | @param mixed $user The user model
    | @param array $validatedData Validated profile data
    | @return void
    |
    */
    private function updateUserProfile($user, array $validatedData): void
    {
        $user->fill($validatedData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Account Deletion Request
    |--------------------------------------------------------------------------
    |
    | Validates that the account deletion request includes the correct
    | current password.
    |
    | @param Request $request The incoming HTTP request
    | @return void
    |
    */
    private function validateAccountDeletion(Request $request): void
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Logout and Delete User
    |--------------------------------------------------------------------------
    |
    | Handles the complete user logout process including session invalidation
    | and account deletion.
    |
    | @param mixed $user The user model
    | @param Request $request The incoming HTTP request
    | @return void
    |
    */
    private function logoutAndDeleteUser($user, Request $request): void
    {
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
