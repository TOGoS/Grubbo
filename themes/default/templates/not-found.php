<?php $this->outputTemplate('page-header',array('title'=>'Document Not Found')); ?>

<p><?php echo htmlspecialchars($resourceName); ?> not found</p>

<?php $this->outputTemplate('page-footer',array()); ?>