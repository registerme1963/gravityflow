<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

if ( ! class_exists( 'Gravity_Flow_Merge_Tag_Workflow_Url' ) ) {
	require_once( 'class-merge-tag-workflow-url.php' );
}

class Gravity_Flow_Merge_Tag_Workflow_Reject_Token extends Gravity_Flow_Merge_Tag_Workflow_Url {

	/**
	 * The name of the merge tag.
	 *
	 * @since 1.7.1-dev
	 *
	 * @var null
	 */
	public $name = 'workflow_reject_token';

	/**
	 * The regular expression to use for the matching.
	 *
	 * @since 1.7.1-dev
	 *
	 * @var string
	 */
	protected $regex = '/{workflow_reject_token}/';

	/**
	 * Replace the {workflow_token_link} merge tags.
	 *
	 * @since 1.7.1-dev
	 *
	 * @param string $text The text being processed.
	 *
	 * @return string
	 */
	public function replace( $text ) {

		$matches = $this->get_matches( $text );

		if ( ! empty( $matches ) ) {

			if ( empty( $this->step ) || empty( $this->assignee ) ) {
				foreach ( $matches as $match ) {
					$full_tag = $match[0];
					$text     = str_replace( $full_tag, '', $text );
				}

				return $text;
			}

			$approve_token = $this->get_token();

			$text = str_replace( '{workflow_reject_token}', $approve_token, $text );

			if ( is_array( $matches ) ) {
				foreach ( $matches as $match ) {
					$full_tag = $match[0];
					$text     = str_replace( $full_tag, $approve_token, $text );
				}
			}
		}

		return $text;
	}

	protected function get_token() {
		$expiration_days = apply_filters( 'gravityflow_approval_token_expiration_days', 2, $this->assignee );

		$expiration_str = '+' . (int) $expiration_days . ' days';

		$expiration_timestamp = strtotime( $expiration_str );

		$scopes = array(
			'pages'           => array( 'inbox' ),
			'step_id'         => $this->step->get_id(),
			'entry_timestamp' => $this->step->get_entry_timestamp(),
			'entry_id'        => $this->step->get_entry_id(),
			'action'          => 'reject',
		);
		$approve_token = gravity_flow()->generate_access_token( $this->assignee, $scopes, $expiration_timestamp );

		return $approve_token;
	}
}

Gravity_Flow_Merge_Tags::register( new Gravity_Flow_Merge_Tag_Workflow_Reject_Token );
