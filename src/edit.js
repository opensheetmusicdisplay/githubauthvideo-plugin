import { InspectorControls } from '@wordpress/block-editor';
import { SelectControl } from '@wordpress/components';
import { withSelect } from "@wordpress/data";
/**
 * Retrieves the translation of text.
 *
 * @see https://developer.worSelectVideoControldpress.org/block-editor/packages/packages-i18n/
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
const Edit = ( {attributes, postList, className, setAttributes} ) => {
	let postSelection = [{label: "Select Video...", value: -1}];
	if(postList && postList.length > 0){
		postSelection = postSelection.concat(postList.map((value, index, array) => {
			return {label: value.title.raw, value: value.id};
		}));
	}
	return (
		<div className={ className }>
			{
				<InspectorControls>
					<SelectControl
						label="Select Video"
						value={ attributes.videoId }
						onChange={ ( val ) => setAttributes( { videoId: val } ) }
						options = { postSelection }
					>
					</SelectControl>
				</InspectorControls>
			}
			<input type="hidden" className="videoId" value = { attributes.videoId }></input>
			<img src= { js_data.player_image } />
		</div>
	);
}

export default withSelect( (select, ownProps) => {
	const { getEntityRecords } = select('core');
	const postQuery = {
		per_page: -1,
		orderby: 'date',
		order: 'asc',
		status: 'publish'
	};
	return  { postList: getEntityRecords('postType', 'github-sponsor-video', postQuery)};
} )(Edit);