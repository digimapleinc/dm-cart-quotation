<?php

/**
 * Generates a cryptographically secure random token for quotations.
 *
 * @param int $length The length of the token to generate (default: 32).
 * @return string A hexadecimal random token.
 */
function generate_quote_hash( $length = 32 ) {
	return bin2hex( random_bytes( $length ) );
}
