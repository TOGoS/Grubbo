<?php

interface Grubbo_Vcs_Committable {
    public function commit( Grubbo_Vcs_CommitInfo $commitInfo );
}
