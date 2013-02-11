<?php

require_once 'Grubbo/Store/ResourceStore.php';
require_once 'Grubbo/Vcs/Committable.php';
require_once 'Grubbo/Vcs/Transactable.php';

interface Grubbo_Vcs_Vcs extends Grubbo_Store_ResourceStore,
	Grubbo_Vcs_Transactable, Grubbo_Vcs_Committable { }
