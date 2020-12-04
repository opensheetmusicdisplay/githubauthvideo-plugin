/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save
 *
 * @return {WPElement} Element to render.
 */
export default function save( {attributes, className, setAttributes} ) {
	return (
		<div>
			<input type="hidden" className="organizationId" value = { attributes.organizationId }></input>
			<input type="hidden" className="tierId" value = { attributes.tierId }></input>
			<input type="hidden" className="videoId" value = { attributes.videoId }></input>
			<video className={ className } width={ attributes.width } height={ attributes.height } >
				<source></source>
			</video>
		</div>
	);
}
