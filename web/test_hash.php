<?php
echo 'Attendu : DHJu3aO5tox49yAcPrXwjJRZQD4wGXI/zCncQ9PkYiTiHPI4OdzCCCpmhceajZcjRCiJQLcAfNXkhnWjxwURPA==';
echo '<br/>';
echo '<br/>';


/*
FOS utilise la méthode encodePassword qui utilise une classe symfony (/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Encoder/MessageDigestPasswordEncoder.php)
qui encode comme ceci :

public function encodePassword($raw, $salt)
{
	if ($this->isPasswordTooLong($raw)) {
		throw new BadCredentialsException('Invalid password.');
	}

	if (!in_array($this->algorithm, hash_algos(), true)) {
		throw new \LogicException(sprintf('The algorithm "%s" is not supported.', $this->algorithm));
	}

	$salted = $this->mergePasswordAndSalt($raw, $salt);
	$digest = hash($this->algorithm, $salted, true);

	// "stretch" hash
	for ($i = 1; $i < $this->iterations; ++$i) {
		$digest = hash($this->algorithm, $digest.$salted, true);
	}

	return $this->encodeHashAsBase64 ? base64_encode($digest) : bin2hex($digest);
}

L'option de sortie par défaut est base64_encode($digest)
*/
// Ce combo password + salt fonctionne sur promed_portal_dev
$password = 'Bierhefoj5';
$salted = $password.'{ba247c3935de250e1be57a1a8b125293}';
//ici l'option à true sort le hash en binary :problème rencontré pour comparer le résultat avec SQL
$digest = hash('sha512', $salted, true);
echo $digest;
echo '<br/><br/>';
//si je test sans l'option j'arrive à faire la même chose en SQL
$digest2 = hash('sha512', $salted, false);
echo '<br/><br/>';
//J'ai testé l'encade base64 dès la 1ère étape et ça ne coïncide 
echo base64_encode($digest);
//Cependant avec l'option à false pas de soucis
//echo base64_encode($digest2);
echo '<br/><br/>';

// On boucle 5000 fois et à chaque fois on concatene la clé initiale

//Vu que ça ne fonctionne pas à la 1ère étape ça ne marche forcément pas à la 5000ème
for ($i = 1; $i < 5000; ++$i) {	
echo base64_encode($digest).'<br/>';
	$digest = hash('sha512', $digest.$salted, true);
}
echo 'Test 1 : '.base64_encode($digest);
unset($digest);
//Ce cas fonctionne, je retombre sur les même données côté SQL logiquement maais ne colle pas avec ce qui se fait sur nos applis sf
echo '<br/><br/>';
for ($i = 1; $i < 5000; ++$i) {
	$digest2 = hash('sha512', $digest2.$salted, false);
}
echo 'Test 2 : '.base64_encode($digest2);
unset($digest2);
echo '<br/><br/>';