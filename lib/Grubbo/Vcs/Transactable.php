<?php

interface Grubbo_Vcs_Transactable {
    public function openTransaction();
    public function closeTransaction();
    public function cancelTransaction();
}
