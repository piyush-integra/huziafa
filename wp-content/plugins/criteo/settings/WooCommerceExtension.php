<?php

defined('ABSPATH') or die('Forbidden');

/**
 * Class that defines WooCommerce admin fields (fields that can be used and displayed in admin page).
 */
class WooCommerceExtension
{
    /**
     * Custom WooCommerce admin field that displays a label and a link (anchor).
     * @param $value - field settings.
     */
    public static function outputLinkField($value)
    {
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label>
            </th>
            <td>
                <a id="<?php echo esc_attr($value['id']); ?>"
                   target="_blank"
                   href="<?php echo esc_html($value['link']); ?>"><?php echo esc_html($value['label']); ?></a>
            </td>
        </tr>
        <?php
    }

    public static function outputVersionDisplayField($value)
    {
        $version = $value['tool_version'];
        if ($value['compatible']) {
            $text = __('Your major version %s was tested with current version of plugin.', 'criteo');
            $text = sprintf($text, $version);
            $description = '<span class="dashicons dashicons-yes" title="' . $text . '"></span>';
        } else {
            $text = __('Your major version %s was not tested with current version of plugin.', 'criteo');
            $text = sprintf($text, $version);
            $description = '<span class="dashicons dashicons-editor-help" title="' . $text . '"></span>';
        }

        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label>
            </th>
            <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                <input
                        name="<?php echo esc_attr( $value['id'] ); ?>"
                        id="<?php echo esc_attr( $value['id'] ); ?>"
                        type="<?php echo esc_attr( 'text' ); ?>"
                        style="<?php echo esc_attr( $value['css'] ); ?>"
                        value="<?php echo esc_attr( $value['label_value'] ); ?>"
                        class="<?php echo esc_attr( $value['class'] ); ?>"
                        readonly
                />
                <?php echo $description; ?>
            </td>
        </tr>
        <?php
    }

    /**
     * * Custom WooCommerce admin field that displays a horizontal line in settings page.
     */
    public static function outputHorizontalLine()
    {
        ?>
        <tr valign="top" style="border-bottom: 1px solid #000; height: 1px;">
            <th style="padding: 0; height: 1px"></th>
            <td style="padding: 0; height: 1px"></td>
        </tr>
        <?php
    }

    /**
     * * Custom WooCommerce admin field that displays a subsection title in settings page.
     */
    public static function outputSubsectionTitle($value)
    {
        ?>
        <tr>
            <td colspan="2"><h3><?php echo esc_html($value['title']); ?></h3></td>
        </tr>
        <?php
    }

}