# recaptcha-php
Classe helper php per verifica recaptcha di Google

Using in Laravel:
```bash
    Nel file composer.json, includere la classe helber globale

    "autoload": {
        ...,
        "files": [
          ...,
          "app/Helpers/Recaptcha.php"
        ]
    }
```

```php
<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;
use Recaptcha;

class RecaptchaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // Se non é ambiente di produzione o di sviluppo
        // non si esegue il controllo del recaptcha
        if (env('APP_ENV') == 'local') :
            return $next($request);
        endif;
        
        // Verifica del recaptcha
        if (empty($request->get('recaptcha_token'))) :
            return response()->json([
            'message' => 'Recaptcha obbligatorio',
            ], 405);
        endif;

        $recaptcha = Recaptcha::verify( config('app.recaptcha_secret_key'),
                                        $request->get('recaptcha_token'));

        if (!$recaptcha->isOk()) :
            return response()->json([
            'message' => 'Recaptcha non verificato',
            'isOk' => $recaptcha->isOk()
            ], 406);
        endif;

        return $next($request);
    }
}

```

### Step

Inclusione classe
```php
use Recaptcha;
```

Inizializzazione istanza Recaptcha con chiamata api in corso
```php
$recaptcha = Recaptcha::verify( 'secret-key-8W37g4aDhu2OZ5L', 'token-frontend-yr14BBt4u6mJb0R');
```

Verifica della risposta di google con un valore booleano
```js
$recaptcha->isOk()
```


