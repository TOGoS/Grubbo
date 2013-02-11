<?php

interface Grubbo_Vcs_Transactable {
	public function openTransaction( $path );
	public function closeTransaction( $path );
	public function cancelTransaction( $path );
}
