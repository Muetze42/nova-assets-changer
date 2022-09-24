# Create register page

Here, finally, is a tutorial on how to use this secretly added feature to register more Nova pages.

This description is a more snipped, and it is recreated the registry of [hf.huth.it](https://hf.huth.it/).

## Instructions

### Create a Controller

`app/Http/Controllers/Nova/RegisterController.php`

```php
<?php

namespace App\Http\Controllers\Nova;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    use AuthenticatesUsers, ValidatesRequests;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('nova.guest:'.config('nova.guard'))->except('logout');
    }

    /**
     * Show Register Form
     *
     * @return \Inertia\Response|Response
     */
    public function showRegisterForm()
    {
        if ($loginPath = config('nova.routes.login', false)) {
            return Inertia::location($loginPath);
        }

        return Inertia::render('Nova.Register', [
            'privacyText' => __('Accept :privacy', ['privacy' => '<a href="https://huth.it/privacy" target="_blank" class="link-default">'.__('privacy policy').'</a>']),
        ]);
    }

    /**
     * Handle Register Request
     *
     * @param Request $request
     * @return bool|JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email', 'max:254', 'unique:users,email', 'confirmed'],
            'name'     => ['required', 'min:3', 'max:30', 'string', 'unique:users,name', 'alpha_dash'],
            'password' => ['required', Password::default(), 'confirmed'],
            'privacy'  => ['accepted'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('Register failed'),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);


        /**
         * Notify Email Verification
         * https://laravel.com/docs/9.x/verification
         */
        //$user->sendEmailVerificationNotification();

        Auth::login($user);

        return true;
    }
}
```

Specificity. I have translated a part of the registration in the controller:

```php
'privacyText' => __('Accept :privacy', ['privacy' => '<a href="https://huth.it/privacy" target="_blank" class="link-default">'.__('privacy policy').'</a>']),
```

### Register new routes for Nova.

`app/Providers/RouteServiceProvider.php`

```php
class RouteServiceProvider extends ServiceProvider
{
    // ...
    
     /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot(): void
    {
        // ....
        Route::middleware('nova')
            ->name('nova.')
            ->prefix(\Laravel\Nova\Nova::path())
            ->domain(config('nova.domain', null))
            ->group(base_path('routes/nova.php'));

    }
    
        // ...
}
```

`routes/nova.php`

```php
<?php

use App\Http\Controllers\Nova\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Nova Routes
|--------------------------------------------------------------------------
|
| Here you can register additional Nova routes for your Nova application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "nova" middleware group. Extend Your Nova!
*/


Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('show-register-form');
Route::post('/register', [RegisterController::class, 'register'])->name('register');
```

### Create register template

`resources/Nova/New/pages/Register.vue`

```vue

<template>
    <div>
        <Head :title="__('Register')"/>

        <form
            @submit.prevent="attempt"
            class="bg-white dark:bg-gray-800 shadow rounded-lg p-8 max-w-[25rem] mx-auto"
        >
            <div class="mb-6">
                <label class="block mb-2" for="name">{{ __('Name') }}<span class="help-text-error">*</span></label>
                <input
                    v-model="form.name"
                    class="form-control form-input form-input-bordered w-full"
                    :class="{ 'form-input-border-error': form.errors.has('name') }"
                    id="name"
                    type="text"
                    name="name"
                    autofocus=""
                    required
                />
                <HelpText class="mt-2 text-red-500" v-if="form.errors.has('name')">
                    {{ form.errors.first('name') }}
                </HelpText>
            </div>

            <div class="mb-2">
                <label class="block mb-2" for="email">{{ __('Email Address') }}<span class="help-text-error">*</span></label>
                <input
                    v-model="form.email"
                    class="form-control form-input form-input-bordered w-full"
                    :class="{ 'form-input-border-error': form.errors.has('email') }"
                    id="email"
                    type="email"
                    name="email"
                    autofocus=""
                    required
                />
                <HelpText class="mt-2 text-red-500" v-if="form.errors.has('email')">
                    {{ form.errors.first('email') }}
                </HelpText>
            </div>
            <div class="mb-6">
                <label class="block mb-2" for="email_confirmation">{{ __('Verify Email Address') }}<span class="help-text-error">*</span></label>
                <input
                    v-model="form.email_confirmation"
                    class="form-control form-input form-input-bordered w-full"
                    :class="{ 'form-input-border-error': form.errors.has('email_confirmation') }"
                    id="email_confirmation"
                    type="email"
                    name="email_confirmation"
                    autofocus=""
                    required
                />
                <HelpText class="mt-2 text-red-500" v-if="form.errors.has('email_confirmation')">
                    {{ form.errors.first('email_confirmation') }}
                </HelpText>
            </div>

            <div class="mb-2">
                <label class="block mb-2" for="password">{{ __('Password') }}<span class="help-text-error">*</span></label>
                <input
                    v-model="form.password"
                    class="form-control form-input form-input-bordered w-full"
                    :class="{ 'form-input-border-error': form.errors.has('password') }"
                    id="password"
                    type="password"
                    name="password"
                    required
                />

                <HelpText class="mt-2 text-red-500" v-if="form.errors.has('password')">
                    {{ form.errors.first('password') }}
                </HelpText>
            </div>

            <div class="mb-6">
                <label class="block mb-2" for="password_confirmation">{{ __('Confirm Password') }}<span class="help-text-error">*</span></label>
                <input
                    v-model="form.password_confirmation"
                    class="form-control form-input form-input-bordered w-full"
                    :class="{ 'form-input-border-error': form.errors.has('password_confirmation') }"
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"

                />

                <HelpText class="mt-2 text-red-500" v-if="form.errors.has('password_confirmation')">
                    {{ form.errors.first('password_confirmation') }}
                </HelpText>
            </div>

            <div class="mb-6">
                <label class="block mb-2" for="privacy">
                    <input
                        v-model="form.privacy"
                        class="checkbox mb-1 mr-1"
                        :class="{ 'form-input-border-error': form.errors.has('privacy') }"
                        id="privacy"
                        type="checkbox"
                        name="privacy"
                        required
                    />
                    <span v-html="privacyText"/>
                    <span class="help-text-error">*</span>
                </label>

                <HelpText class="mt-2 text-red-500" v-if="form.errors.has('privacy')">
                    {{ form.errors.first('privacy') }}
                </HelpText>
            </div>

            <DefaultButton class="w-full flex justify-center mb-6" type="submit">
                <span>
                    {{ __('Register') }}
                </span>
            </DefaultButton>

            <div class="flex justify-between mt-6">
                <Link
                    :href="$url('/login')"
                    class="text-gray-500 font-bold no-underline"
                    v-text="__('Login')"
                />
                <div
                    v-if="supportsPasswordReset || forgotPasswordPath !== false"
                    class="ml-auto"
                >
                    <Link
                        v-if="forgotPasswordPath === false"
                        :href="$url('/password/reset')"
                        class="text-gray-500 font-bold no-underline"
                        v-text="__('Forgot your password?')"
                    />
                    <a
                        v-else
                        :href="forgotPasswordPath"
                        class="text-gray-500 font-bold no-underline"
                        v-text="__('Forgot your password?')"
                    />
                    <a
                        v-else
                        :href="forgotPasswordPath"
                        class="text-gray-500 font-bold no-underline"
                        v-text="__('Forgot your password?')"
                    />
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import Auth from '@/layouts/Auth'

export default {
    name: "RegisterPage",

    layout: Auth,

    data: () => ({
        form: Nova.form({
            name: '',
            email: '',
            email_confirmation: '',
            password: '',
            password_confirmation: '',
            privacy: false,
        }),
    }),

    props: {
        privacyText: String,
    },

    methods: {
        async attempt() {
            const {redirect} = await this.form.post(Nova.url('/register'))

            if (redirect !== undefined && redirect !== null) {
                window.location.href = redirect
            }

            window.location.href = Nova.config('base')
            //Nova.visit('/') // Missing dashboard auth
        },
    },

    computed: {
        supportsPasswordReset() {
            return Nova.config('withPasswordReset')
        },

        forgotPasswordPath() {
            return Nova.config('forgotPasswordPath')
        },
    },
}
</script>
```

### Add register link to login template

`resources/Nova/Nova/js/pages/Login.vue`

```vue

<template>
    <div>
        <Head :title="__('Log In')"/>

        <form
            @submit.prevent="attempt"
            class="bg-white dark:bg-gray-800 shadow rounded-lg p-8 max-w-[25rem] mx-auto"
        >
            <h2 class="text-2xl text-center font-normal mb-6 text-90">
                {{ __('Welcome Back!') }}
            </h2>

            <DividerLine/>

            <div class="mb-6">
                <label class="block mb-2" for="email">{{ __('Email Address') }}</label>
                <input
                    v-model="form.email"
                    class="form-control form-input form-input-bordered w-full"
                    :class="{ 'form-input-border-error': form.errors.has('email') }"
                    id="email"
                    type="email"
                    name="email"
                    autofocus=""
                    required
                />

                <HelpText class="mt-2 text-red-500" v-if="form.errors.has('email')">
                    {{ form.errors.first('email') }}
                </HelpText>
            </div>

            <div class="mb-6">
                <label class="block mb-2" for="password">{{ __('Password') }}</label>
                <input
                    v-model="form.password"
                    class="form-control form-input form-input-bordered w-full"
                    :class="{ 'form-input-border-error': form.errors.has('password') }"
                    id="password"
                    type="password"
                    name="password"
                    required
                />

                <HelpText class="mt-2 text-red-500" v-if="form.errors.has('password')">
                    {{ form.errors.first('password') }}
                </HelpText>
            </div>

            <div class="flex mb-6">
                <CheckboxWithLabel
                    :checked="form.remember"
                    @input="() => (form.remember = !form.remember)"
                >
                    {{ __('Remember me') }}
                </CheckboxWithLabel>

            </div>

            <DefaultButton class="w-full flex justify-center" type="submit">
                <span>
                    {{ __('Log In') }}
                </span>
            </DefaultButton>

            <div class="flex justify-between mt-6">
                <Link
                    :href="$url('/register')"
                    class="text-gray-500 font-bold no-underline pl-2"
                    v-text="__('Register')"
                />
                <div
                    v-if="supportsPasswordReset || forgotPasswordPath !== false"
                    class="ml-auto"
                >
                    <Link
                        v-if="forgotPasswordPath === false"
                        :href="$url('/password/reset')"
                        class="text-gray-500 font-bold no-underline"
                        v-text="__('Forgot your password?')"
                    />
                    <a
                        v-else
                        :href="forgotPasswordPath"
                        class="text-gray-500 font-bold no-underline"
                        v-text="__('Forgot your password?')"
                    />
                    <a
                        v-else
                        :href="forgotPasswordPath"
                        class="text-gray-500 font-bold no-underline"
                        v-text="__('Forgot your password?')"
                    />
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import Auth from '@/layouts/Auth'

export default {
    name: 'LoginPage',

    layout: Auth,

    data: () => ({
        form: Nova.form({
            email: '',
            password: '',
            remember: false,
        }),
    }),

    methods: {
        async attempt() {
            try {
                const {redirect} = await this.form.post(Nova.url('/login'))

                let path = '/'

                if (redirect !== undefined && redirect !== null) {
                    path = {url: redirect, remote: true}
                }

                Nova.visit(path)
            } catch (error) {
                if (error.response?.status === 500) {
                    Nova.error(this.__('There was a problem submitting the form.'))
                }
            }
        },
    },

    computed: {
        supportsPasswordReset() {
            return Nova.config('withPasswordReset')
        },

        forgotPasswordPath() {
            return Nova.config('forgotPasswordPath')
        },
    },
}
</script>
```

## Example

Here you can find a working Laravel installation with Nova & a registration page following this guide:

[github.com/Muetze42/nova-assets-changer-register-page](https://github.com/Muetze42/nova-assets-changer-register-page)
