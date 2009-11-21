<?php

require_once 'Grubbo/Committable.php';
require_once 'Grubbo/Store.php';
require_once 'Grubbo/Transactable.php';

interface Grubbo_Vcs_Vcs extends Grubbo_Store, Grubbo_Transactable, Grubbo_Committable { }
