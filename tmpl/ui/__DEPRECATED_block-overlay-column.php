<?php //if ( skp_is_customizing() ) : ?>
<?php
  $parent_model = SEK_Front() -> parent_model;
  $parent_is_last_allowed_nested = is_array( $parent_model ) && array_key_exists('is_last', $parent_model ) && true == $parent_model['is_last'];
  $parent_can_have_more_columns = is_array( $parent_model['collection'] ) && count( $parent_model['collection'] ) < 12;
  $is_single_column = is_array( $parent_model['collection'] ) && 1 >= count( $parent_model['collection'] );
//error_log( print_r( $parent_model, true ) );
?>
<div class="sek-block-overlay">
  <div class="sek-block-overlay-header">
    <div class="sek-block-overlay-actions">
      <i class="fas fa-arrows-alt sek-move-column" title="<?php _e( 'Move', 'sek-builder' ); ?>"></i>
      <i data-sek-action="edit-options" class="fas fa-cogs sek-action" title="<?php _e( 'Options', 'sek-builder' ); ?>"></i>
      <i data-sek-action="pick-module" class="fas fa-plus-circle sek-action" title="<?php _e( 'Add Module', 'sek-builder' ); ?>"></i>
      <?php if ( $parent_can_have_more_columns ) : ?>
        <i data-sek-action="duplicate" class="far fa-clone sek-action" title="<?php _e( 'Duplicate', 'sek-builder' ); ?>"></i>
      <?php endif; ?>
      <?php if ( ! $parent_is_last_allowed_nested ) : ?>
        <i data-sek-action="add-section" class="fas far fa-plus-square sek-action" title="<?php _e( 'Add Sektion', 'sek-builder' ); ?>"></i>
      <?php endif; ?>
      <?php if ( ! $parent_is_single_column ) : ?>
        <i data-sek-action="remove" class="far fa-trash-alt sek-action" title="<?php _e( 'Remove', 'sek-builder' ); ?>"></i>
      <?php endif; ?>
    </div>
    <div class="sek-clear"></div>
  </div>
</div>
<?php //endif; ?>