<?php //if ( skp_is_customizing() ) : ?>
<?php
  $model = SEK_Front() -> model;
  $can_have_more_columns = is_array( $model['collection'] ) && count( $model['collection'] ) < 12;
  $is_last_possible_section = array_key_exists('is_last', $model) && true == $model['is_last'];
  // error_log('<block-overlay-sektion.php>');
  // error_log( print_r( $model, true ) );
  // error_log('</block-overlay-sektion.php>');
?>
<div class="sek-block-overlay">
  <div class="sek-block-overlay-header">
    <div class="sek-block-overlay-actions">
      <?php // if this is a nested section, it has the is_last property set to true. We don't want to make it movable for the moment. @todo ?>
      <?php if ( ! $is_last_possible_section ) : ?>
        <i class="fas fa-arrows-alt sek-move-section" title="<?php _e( 'Move', 'sek-builder' ); ?>"></i>
      <?php endif; ?>
      <i data-sek-action="edit-options" class="fas fa-cogs sek-action" title="<?php _e( 'Options', 'sek-builder' ); ?>"></i>
      <?php if ( $can_have_more_columns ) : ?>
        <i data-sek-action="add-column" class="fas fa-plus-circle sek-action" title="<?php _e( 'Add Column', 'sek-builder' ); ?>"></i>
      <?php endif; ?>
      <i data-sek-action="duplicate" class="far fa-clone sek-action" title="<?php _e( 'Duplicate', 'sek-builder' ); ?>"></i>
      <i data-sek-action="remove" class="far fa-trash-alt sek-action" title="<?php _e( 'Remove', 'sek-builder' ); ?>"></i>
    </div>
    <div class="sek-clear"></div>
  </div>
</div>
<?php //endif; ?>