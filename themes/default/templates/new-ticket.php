<?php $this->outputTemplate('page-header'); ?>

<form>
<input type="hidden" name="action" value="post"/>
<input type="hidden" name="doc/ticket" value="true"/>
<input type="hidden" name="doc/format" value="wiki"/>
Title: <input type="text" name="doc/title" value="" size="60"/><br />
<textarea name="content" rows="20" cols="80"></textarea><br />
<input type="submit" value="Submit"/>
</form>

<?php $this->outputTemplate('page-footer'); ?>
