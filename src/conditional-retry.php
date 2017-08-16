<?php

if( ! function_exists('retryConditional')) {

	/**
	 * Retries callable with error detection via callback
	 *
	 * Could be specified how many times, default is 1 time.
	 *
	 * @param callable $what
	 * @param callable $errorCondition $callback($returnValue, $exception)
	 * @param int $runs how many times should callback $what invoked
	 *
	 * @return mixed
	 * @throws Throwable
	 */
	function retryConditional(callable $what, callable $errorCondition, int $runs = 1) {
		$result           = null;
		$currentException = null;
		again:
		try {
			$result = $what();
		} catch (Throwable $exception){
			$currentException = $exception;
		}

		if($errorCondition($result, $currentException)) {
			if($runs-- > 1) {
				$currentException = null;
				goto again;
			}
		}
		if($currentException) {
			throw $currentException;
		}

		return $result;
	}
}
