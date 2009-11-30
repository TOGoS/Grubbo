<?php $this->outputTemplate('page-header',array('pageTitle'=>$pageTitle)); ?>

<?php if( $errorHtml ) { ?>
<div class="error-messages">
<?php echo $errorHtml; ?>
</div>
<?php } ?>

<form method="POST">
<?php if( $redirect ) { ?>
 <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>"/>
<?php } ?>
 <table class="form-pairs">
  <tr><th>Redirect</th><td><?php echo htmlspecialchars($redirect); ?></td></tr>
  <tr><th>Username</th><td><input type="text" name="username"/></td></tr>
  <tr><th>Password</th><td><input type="password" name="password"/></td></tr>
  <tr><td colspan="2"><input type="submit" name="update" value="Log in"/></td></tr>
 </table>
</form>


<?php $this->outputTemplate('page-footer',array()); ?>
