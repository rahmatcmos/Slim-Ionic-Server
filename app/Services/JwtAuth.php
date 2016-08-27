<?php

namespace App\Services;

use App\Models\Jwt;
use App\Models\User;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Carbon\Carbon;

class JwtAuth
{

	public function checkJWT($token)
	{
		$jwt = Jwt::find($token); 
		if($jwt)
			$jwt->update(['updated_at' => Carbon::now()->toDateTimeString()]);
		return $jwt;
	}

	public function requestJWT($email,$password,$time,$issuer,$audience,$jti,$nbf,$exp,$secret,$expired_at,$os)
	{
		$user = User::where('email',$email)->first();

		if (!$user) {
			return false;
		}

		if ($user->status === "BAN" || $user->status === "INACTIVE") {
			return $user->status;
		}

		if (password_verify($password,$user->password)) {
			$signer = new Sha256();
			$token = (new Builder())->setIssuer($issuer) // Configures the issuer (iss claim)
						->setAudience($audience) // Configures the audience (aud claim)
						->setId($jti, true) // Configures the id (jti claim), replicating as a header item
						->setIssuedAt($time) // Configures the time that the token was issue (iat claim)
						->setNotBefore($time + $nbf) // Configures the time that the token can be used (nbf claim)
						->setExpiration($time + $exp) // Configures the expiration time of the token (nbf claim)
						->set('uid', $user->id) // Configures a new claim, called "uid"
						->set('name', $user->name)
						->sign($signer, $secret) // creates a signature using secret key
						->getToken(); // Retrieves the generated token

			Jwt::create([
				'id' => $jti,
				'user' => $user->id,
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'audience' => $audience,
				'os' => $os,
				'expired_at' => $expired_at,
			]);

			return (String) $token;
		}

		return false;
	}

}
