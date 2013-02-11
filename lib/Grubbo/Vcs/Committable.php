<?php

interface Grubbo_Vcs_Committable {
	/**
	 * @param string $path the path within this committable of the thing being changed
	 * @param Grubbo_Vcs_CommitInfo $commitInfo commit information
	 */
	public function commit( $path, Grubbo_Vcs_CommitInfo $commitInfo );
}
