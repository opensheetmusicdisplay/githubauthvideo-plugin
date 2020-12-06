import { BlockSettingsMenuControls } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import  * as videojs  from 'video.js';
import Cookies from 'js-cookie';
/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @param {Object} [props]           Properties passed from the editor.
 * @param {string} [props.className] Class name generated for the block.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit( {attributes, className, setAttributes} ) {
	let classes = 'video-js ' + className;
	return (
		<div>
			{
				<BlockSettingsMenuControls>
					<TextControl
						label="Video ID"
						value={ attributes.videoId }
						onChange={ ( val ) => setAttributes( { videoId: val } ) }
					>
					</TextControl>
				</BlockSettingsMenuControls>
			}
			<input type="hidden" className="videoId" value = { attributes.videoId }></input>
			<video className={ classes } width={ attributes.width } height={ attributes.height } >
				<source src={"github_auth_video?=" + attributes.videoId}></source>
			</video>
		</div>
	);
}
