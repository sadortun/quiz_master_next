<?php
/**
 * Creates the results page tab when editing quizzes.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the Results Page tab to the Quiz Settings page.
 *
 * @since 6.1.0
 */
function qsm_options_results_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Results Pages', 'quiz-master-next' ), 'qsm_options_results_tab_content' );
}
add_action( 'plugins_loaded', 'qsm_options_results_tab', 5 );

/**
 * Adds the Results page content to the Results tab.
 *
 * @since 6.1.0
 */
function qsm_options_results_tab_content() {
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = intval( $_GET['quiz_id'] );
	$js_data = array(
		'quizID' => $quiz_id,
		'nonce'  => wp_create_nonce( 'wp_rest' ),
	);
	wp_enqueue_script( 'qsm_results_admin_script', plugins_url( '../../js/qsm-admin-results.js', __FILE__ ), array( 'jquery-ui-sortable', 'qmn_admin_js' ), $mlwQuizMasterNext->version );
	wp_localize_script( 'qsm_results_admin_script', 'qsmResultsObject', $js_data );
	wp_enqueue_editor();
	wp_enqueue_media();
	?>
	<h2><?php esc_html_e( 'Results Pages', 'quiz-master-next' ); ?></h2>
	<p>Need assistance with this tab? <a href="https://quizandsurveymaster.com/docs/creating-quizzes-and-surveys/setting-up-results-pages/" target="_blank">Check out the documentation</a> for this tab!</p>
        
	<!-- Results Page Section -->
	<section>
		<h3>Your Pages</h3>
		<button class="save-pages button-primary"><?php esc_html_e( 'Save Results Pages', 'quiz-master-next' ); ?></button>
                <span class="spinner" style="float: none;"></span>
		<button class="add-new-page button"><?php esc_html_e( 'Add New Results Page', 'quiz-master-next' ); ?></button>
                &nbsp;<a class="qsm-show-all-variable-text" href="#"><?php _e('Show Template Variables', 'quiz-master-next'); ?></a>
		<div id="results-pages"></div>
		<button class="save-pages button-primary"><?php esc_html_e( 'Save Results Pages', 'quiz-master-next' ); ?></button>
                <span class="spinner" style="float: none;"></span>
		<button class="add-new-page button"><?php esc_html_e( 'Add New Results Page', 'quiz-master-next' ); ?></button>
	</section>

	<!-- Templates -->
	<script type="text/template" id="tmpl-results-page">
		<div class="results-page">
			<header class="results-page-header">
				<div><button class="delete-page-button"><span class="dashicons dashicons-trash"></span></button></div>
			</header>
			<main class="results-page-content">
				<div class="results-page-when">
					<div class="results-page-content-header">
						<h4>When...</h4>
						<p>Set conditions for when this page should be shown. Leave empty to set this as the default page.</p>
					</div>
					<div class="results-page-when-conditions">
						<!-- Conditions go here. Review template below. -->
					</div>
					<button class="new-condition button"><?php esc_html_e( 'Add additional condition', 'quiz-master-next' ); ?></button>
				</div>
				<div class="results-page-show">
					<div class="results-page-content-header">
						<h4>...Show</h4>
						<p>Create the results page that should be shown when the conditions are met.</p>
					</div>
					<textarea id="results-page-{{ data.id }}" class="results-page-template">{{{ data.page }}}</textarea>
					<p>Or, redirect the user by entering the URL below:</p>
					<input type="text" class="results-page-redirect" value="<# if ( data.redirect ) { #>{{ data.redirect }}<# } #>">
				</div>
			</main>
		</div>
	</script>

	<script type="text/template" id="tmpl-results-page-condition">
		<div class="results-page-condition">
			<button class="delete-condition-button"><span class="dashicons dashicons-trash"></span></button>
			<select class="results-page-condition-criteria">
				<option value="points" <# if (data.criteria == 'points') { #>selected<# } #>>Total points earned</option>
				<option value="score" <# if (data.criteria == 'score') { #>selected<# } #>>Correct score percentage</option>
				<?php do_action( 'qsm_results_page_condition_criteria' ); ?>
			</select>
			<select class="results-page-condition-operator">
				<option value="equal" <# if (data.operator == 'equal') { #>selected<# } #>>is equal to</option>
				<option value="not-equal" <# if (data.operator == 'not-equal') { #>selected<# } #>>is not equal to</option>
				<option value="greater-equal" <# if (data.operator == 'greater-equal') { #>selected<# } #>>is greater than or equal to</option>
				<option value="greater" <# if (data.operator == 'greater') { #>selected<# } #>>is greater than</option>
				<option value="less-equal" <# if (data.operator == 'less-equal') { #>selected<# } #>>is less than or equal to</option>
				<option value="less" <# if (data.operator == 'less') { #>selected<# } #>>is less than</option>
				<?php do_action( 'qsm_results_page_condition_operator' ); ?>
			</select>
			<input type="text" class="results-page-condition-value" value="{{ data.value }}">
		</div>
	</script>
        <!--Template popup-->
        <div class="qsm-popup qsm-popup-slide" id="show-all-variable" aria-hidden="false">
            <div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">
                <div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-3-title">
                    <header class="qsm-popup__header">
                            <h2 class="qsm-popup__title"><?php _e('Template Variables', 'quiz-master-next'); ?></h2>                            
                    </header>
                    <main class="qsm-popup__content" id="show-all-variable-content">
                        <?php
                        $variable_list = qsm_text_template_variable_list();
                        $email_exta_variable = array(
                            '%CONTACT_X%' => __( 'Value user entered into contact field. X is # of contact field. For example, first contact field would be %CONTACT_1%', 'quiz-master-next' ),
                            '%CONTACT_ALL%' => __( 'Value user entered into contact field. X is # of contact field. For example, first contact field would be %CONTACT_1%', 'quiz-master-next' ),                            
                        );  
                        $variable_list = array_merge($email_exta_variable, $variable_list);
                        $variable_list['%AVERAGE_CATEGORY_POINTS_X%'] = __('X: Category name - The average amount of points a specific category earned.', 'quiz-master-next');
                        $variable_list['%FACEBOOK_SHARE%'] = __('Displays button to share on Facebook.', 'quiz-master-next');
                        $variable_list['%TWITTER_SHARE%'] = __('Displays button to share on Twitter.', 'quiz-master-next');
                        $variable_list['%POLL_RESULTS_X%'] = __('X = Question ID Note: only supported for multiple choice answers', 'quiz-master-next');
                        $variable_list['%RESULT_ID%'] = __('Show result id', 'quiz-master-next');
                        $variable_list['%QUESTION_ANSWER_X%'] = __('X = Question ID. It will show result of particular question.', 'quiz-master-next');
                        unset($variable_list['%QUESTION%']);
                        unset($variable_list['%USER_ANSWER%']);
                        unset($variable_list['%CORRECT_ANSWER%']);
                        unset($variable_list['%USER_COMMENTS%']);
                        unset($variable_list['%CORRECT_ANSWER_INFO%']);
                        unset($variable_list['%CURRENT_DATE%']);
                        if( $variable_list ){
                            foreach ( $variable_list as $key => $s_variable ) { ?>
                                <div class="popup-template-span-wrap">
                                    <span class="qsm-text-template-span">
                                        <button class="button button-default"><?php echo $key; ?></button>                                    
                                        <span class="dashicons dashicons-editor-help qsm-tooltips-icon">
                                            <span class="qsm-tooltips"><?php echo $s_variable; ?></span>
                                        </span>                                    
                                    </span>
                                </div>
                            <?php                     
                            }
                        }
                        ?>
                    </main>
                    <footer class="qsm-popup__footer" style="text-align: right;">                            
                            <button class="button button-default" data-micromodal-close="" aria-label="Close this dialog window"><?php _e('Close', 'quiz-master-next'); ?></button>
                    </footer>
                </div>
            </div>
        </div>
	<?php
}
?>
