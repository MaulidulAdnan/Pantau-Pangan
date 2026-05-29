<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MerchantProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Register user biasa
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'role' => 'user',
            'status' => 'active',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Register pedagang (perlu approval admin)
     */
    public function registerMerchant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'store_name' => 'required|string|max:255',
            'store_address' => 'nullable|string|max:500',
            'market_id' => 'required|exists:markets,id',
            'description' => 'nullable|string|max:1000',
            'shop_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'shop_photo.required' => 'Foto toko wajib diunggah',
            'shop_photo.image' => 'File harus berupa gambar',
            'shop_photo.mimes' => 'Format gambar harus JPEG, PNG, atau WebP',
            'shop_photo.max' => 'Ukuran foto maksimal 2MB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'role' => 'pedagang',
            'status' => 'active',
        ]);

        $shopPhotoPath = null;
        if ($request->hasFile('shop_photo')) {
            $shopPhotoPath = $request->file('shop_photo')->store('shop-photos', 'public');
        }

        MerchantProfile::create([
            'user_id' => $user->id,
            'store_name' => $request->store_name,
            'store_address' => $request->store_address,
            'market_id' => $request->market_id,
            'description' => $request->description,
            'shop_photo' => $shopPhotoPath,
            'status' => 'pending',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registrasi pedagang berhasil. Menunggu verifikasi admin.',
            'user' => $user->load('merchantProfile'),
            'token' => $token,
        ], 201);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        $user = JWTAuth::user();

        if (!$user) {
            $user = User::where('email', $request->email)->first();
        }

        if ($user && $user->status === 'suspended') {
            JWTAuth::invalidate($token);
            return response()->json(['message' => 'Akun Anda telah di-suspend'], 403);
        }

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user->load('merchantProfile', 'region'),
            'token' => $token,
        ]);
    }

    /**
     * Logout
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Logout berhasil']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal logout'], 500);
        }
    }

    /**
     * Get current user profile
     */
    public function me()
    {
        $user = auth('api')->user();
        return response()->json([
            'user' => $user->load('merchantProfile.market', 'region'),
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string|max:500',
            'gender' => 'nullable|string|in:Laki-laki,Perempuan,Lainnya',
            'address' => 'nullable|string|max:1000',
            'region_id' => 'nullable|exists:regions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['name', 'phone', 'avatar', 'gender', 'address', 'region_id']));

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => $user->fresh()->load('region'),
        ]);
    }

    /**
     * Upload profile photo
     */
    public function uploadProfilePhoto(Request $request)
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ], [
            'photo.required' => 'File foto wajib diunggah',
            'photo.image' => 'File harus berupa gambar',
            'photo.mimes' => 'Format gambar harus JPEG, PNG, atau WebP',
            'photo.max' => 'Ukuran foto maksimal 2MB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Delete old photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Store new photo
        $path = $request->file('photo')->store('profile-photos', 'public');

        $user->update(['profile_photo' => $path]);

        return response()->json([
            'message' => 'Foto profil berhasil diunggah',
            'user' => $user->fresh()->load('region'),
            'photo_url' => asset('storage/' . $path),
        ]);
    }

    /**
     * Remove profile photo
     */
    public function removeProfilePhoto()
    {
        $user = auth('api')->user();

        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
            $user->update(['profile_photo' => null]);
        }

        return response()->json([
            'message' => 'Foto profil berhasil dihapus',
            'user' => $user->fresh()->load('region'),
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth('api')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password saat ini salah'], 422);
        }

        $user->update(['password' => $request->password]);

        return response()->json(['message' => 'Password berhasil diubah']);
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json(['token' => $newToken]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal refresh token'], 401);
        }
    }

    /**
     * Update Merchant Profile (store details)
     */
    public function updateMerchantProfile(Request $request)
    {
        $user = auth('api')->user();
        if (!$user->isPedagang()) {
            return response()->json(['message' => 'Hanya untuk pedagang'], 403);
        }

        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255',
            'store_address' => 'nullable|string|max:500',
            'market_id' => 'required|exists:markets,id',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profile = $user->merchantProfile;
        if (!$profile) {
            return response()->json(['message' => 'Profil pedagang tidak ditemukan'], 404);
        }

        $market = \App\Models\Market::find($request->market_id);
        $newRegionId = $market ? $market->region_id : null;

        $existingStore = \App\Models\MerchantStore::where('user_id', $user->id)
            ->where('region_id', '!=', $newRegionId)
            ->first();

        if ($existingStore) {
            return response()->json([
                'message' => 'Gagal mengubah pasar. Toko Anda lainnya berada di daerah ' . \App\Models\Region::find($existingStore->region_id)->name . '. Hubungi admin jika ingin mengubah daerah operasional utama.',
            ], 422);
        }

        // Auto-pending status if store name or market is modified to enforce re-verification
        $reverify = false;
        if ($profile->store_name !== $request->store_name || $profile->market_id != $request->market_id) {
            $profile->status = 'pending';
            $profile->verified_at = null;
            $reverify = true;
        }

        $profile->update([
            'store_name' => $request->store_name,
            'store_address' => $request->store_address,
            'market_id' => $request->market_id,
            'description' => $request->description,
        ]);

        $primaryStore = \App\Models\MerchantStore::where('user_id', $user->id)->first();
        if ($primaryStore) {
            $primaryStore->update([
                'store_name' => $request->store_name,
                'store_address' => $request->store_address,
                'market_id' => $request->market_id,
                'region_id' => $newRegionId,
                'status' => $reverify ? 'pending' : $primaryStore->status,
            ]);
        }

        return response()->json([
            'message' => $reverify 
                ? 'Profil toko pedagang berhasil diperbarui. Status akun kembali pending untuk verifikasi admin.'
                : 'Profil toko pedagang berhasil diperbarui',
            'profile' => $profile->load('market.region'),
        ]);
    }
}
