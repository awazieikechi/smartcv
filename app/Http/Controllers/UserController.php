<?php

namespace App\Http\Controllers;

use App\Models\PdfDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function _construct()
    {
        $this->middleware(
            [
                'auth:api',
                'scopes:create',
            ])->except(['edit', 'delete', 'update']);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

        ]);
    }

    public function register(Request $requst)
    {
        $validator = $this->validator(request()->all());
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return response(['error' => $errors[0]], Response::HTTP_BAD_REQUEST); //
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),

        ]);

        if ($user) {
            return response([
                'status' => 'success',
                'user' => $user,
            ],
                200);
        }

    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'sometimes|string|email',
            'password' => 'required|string',
        ]);
        $credentials = [];
        $user = null;

        if ($request->has('email')) {
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->firstOrFail();
        }

        if (Hash::check($credentials['password'], $user->password)) {
            $token_for_mobile = $this->generateUserAccessToken($user);

            return response()->json([
                'message' => 'Welcome Back!',
                'token' => $token_for_mobile,
                'data' => $user,

            ]);
        }

        return response()->json(['error' => 'Oops! You have entered invalid credentials'], 400);
    }

    public function profile(Request $request)
    {
        $skills_results = $this->pdfSearchTerm($request, 'skills');

        $job_experience_results = $this->pdfSearchTerm($request, 'Job experience');

        $education_background_results = $this->pdfSearchTerm($request, 'job experience');

        Profile::create([
            'job_experience' => $job_experience_results,
            'skills' => $skills_results,
            'education_background' => $education_background_results,
            'user_id' => $request->id,
        ]);

    }

    public function uploadCV(Request $request)
    {
        $request->validate([
            "file" => "required|mimetypes:application/pdf|max:10000",
        ]);

        $user = User::where('id', $request->id)->first();
        $file = $request->file;
        $file_name = time() . str_shuffle('Class') . str_rand(10) . '.' . $file->getClientOriginalExtension();
        $url = $file->move(public_path() . '/assets/files/', $file_name);

        StorePdfDocumentAsText::dispatch($file_name, $user);

    }

    private function pdfSearchTerm(Request $request, $term)
    {
        return PdfDocument::search($term)
            ->where('user_id', $request->id) //additionnal clauses
            ->get();
    }
}
