<?php

class CustomValidator extends Illuminate\Validation\Validator {
	/**
	 * Validate that an attribute contains only alphabetic characters.
	 * u$B%*%W%7%g%s$r(B(unicode)$B$r30$9!#%*!<%P!<%i%$%I!#(B
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateAlpha($attribute, $value)
	{
		return preg_match('/^\pL+$/', $value);
	}

	/**
	 * Validate that an attribute contains only alpha-numeric characters.
	 * u$B%*%W%7%g%s$r(B(unicode)$B$r30$9!#%*!<%P!<%i%$%I!#(B
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateAlphaNum($attribute, $value)
	{
		return preg_match('/^[\pL\pN]+$/', $value);
	}

	/**
	 * Validate that an attribute contains only alpha-numeric characters, dashes, and underscores.
	 * u$B%*%W%7%g%s$r(B(unicode)$B$r30$9!#%*!<%P!<%i%$%I!#(B
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateAlphaDash($attribute, $value)
	{
		return preg_match('/^[\pL\pN_-]+$/', $value);
	}

    /**
     * $BH>3Q1Q;z(B&$B%9%Z!<%9(B
     */
    protected function validateAlphaSpace($attribute, $value) {
        return preg_match('/^[\pL\s]+$/',$value);
    }
}
