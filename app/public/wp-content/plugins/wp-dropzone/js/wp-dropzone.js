/**
 * WP Dropzone JavaScript Integration
 *
 * Initializes Dropzone instances for WordPress file uploads with drag-and-drop functionality.
 * Reads configuration from data-config attribute instead of global wpDzI18n object.
 *
 * @package
 * @since 1.1.0
 */

/**
 * Initialize dropzone instances after DOM is ready
 *
 * Waits for DOM to be fully loaded before initializing Dropzone instances
 * to ensure data-config attributes are available.
 */
document.addEventListener( 'DOMContentLoaded', function () {
	const dropzones = document.querySelectorAll( '.dropzone[data-config]' );

	dropzones.forEach( function ( dropzone ) {
		try {
			const configData = JSON.parse( dropzone.getAttribute( 'data-config' ) );

			/**
			 * Initialize Dropzone manually on the dropzone element
			 *
			 * Creates a new Dropzone instance with configuration from data-config attribute.
			 * This replaces the previous wpDzI18n global object approach.
			 */
			new Dropzone( '#' + dropzone.id, {
				url: configData.ajax_url + '?action=wp_dropzone_upload_media',
				paramName: 'file',
				maxFilesize: configData.max_file_size,
				addRemoveLinks: configData.remove_links,
				clickable: configData.clickable === 'true',
				acceptedFiles: configData.accepted_files,
				autoProcessQueue: configData.auto_process === 'true',
				maxFiles: configData.max_files,
				resizeWidth: configData.resize_width,
				resizeHeight: configData.resize_height,
				resizeQuality: configData.resize_quality,
				resizeMethod: configData.resize_method,
				thumbnailWidth: configData.thumbnail_width,
				thumbnailHeight: configData.thumbnail_height,
				thumbnailMethod: configData.thumbnail_method,
				/**
				 * Dropzone initialization callback
				 *
				 * Sets up event handlers and configuration after Dropzone is initialized.
				 * Handles manual processing, user authentication, and custom callbacks.
				 */
				init() {
					const closure = this;

					// Handle manual processing when auto_process is false
					if ( configData.auto_process === 'false' ) {
						const processButton = document.getElementById( 'process-' + configData.id );

						if ( processButton ) {
							processButton.addEventListener( 'click', function () {
								closure.processQueue();
							} );
						}
					}

					// Disable dropzone if user is not logged in or doesn't have upload permission
					if ( Boolean( configData.is_user_logged_in ) !== true ) {
						this.disable();
					} else if ( Boolean( configData.can_upload_files ) !== true ) {
						this.disable();
					}

					// Parse and register custom callbacks with security validation
					if (
						configData.callback &&
						typeof configData.callback === 'string' &&
						configData.callback.trim()
					) {
						// Whitelist of allowed Dropzone event names
						const allowedEvents = [
							'addedfile',
							'removedfile',
							'thumbnail',
							'error',
							'errormultiple',
							'success',
							'successmultiple',
							'processing',
							'processingmultiple',
							'uploadprogress',
							'totaluploadprogress',
							'sending',
							'sendingmultiple',
							'queuecomplete',
							'complete',
							'completemultiple',
							'canceled',
							'canceledmultiple',
							'maxfilesreached',
							'maxfilesexceeded',
							'dragenter',
							'dragover',
							'dragleave',
							'drop',
							'paste',
							'reset',
							'init',
						];

						// Split callbacks by comma, handling function closures
						const callbackPattern =
							/\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*:\s*function\s*\([^)]*\)\s*\{[^}]*\}\s*/g;
						const matches = configData.callback.matchAll( callbackPattern );

						for ( const match of matches ) {
							// Validate match structure exists
							if ( ! match || ! match[ 1 ] || ! match[ 0 ] ) {
								continue;
							}

							const eventName = match[ 1 ].trim();
							const fullMatch = match[ 0 ].trim();

							// Validate event name and full match are not empty
							if ( ! eventName || ! fullMatch ) {
								continue;
							}

							// Validate event name is in whitelist
							if ( ! allowedEvents.includes( eventName ) ) {
								console.warn(
									'WP Dropzone: Invalid event name in callback:',
									eventName,
								);
								continue;
							}

							// Additional security check: block only the most dangerous patterns
							// Allow legitimate DOM manipulation (getElementById, querySelector, .value, etc.)
							const dangerousPatterns = [
								/eval\s*\(/i,
								/new\s+Function\s*\(/i,
								/setTimeout\s*\(\s*["']/i,
								/setInterval\s*\(\s*["']/i,
								/document\.write\s*\(/i,
								/document\.writeln\s*\(/i,
								/\.innerHTML\s*=/i,
								/\.outerHTML\s*=/i,
								/insertAdjacentHTML\s*\(/i,
								/<script/i,
								/javascript:/i,
							];

							let isSafe = true;
							for ( const pattern of dangerousPatterns ) {
								if ( pattern.test( fullMatch ) ) {
									isSafe = false;
									break;
								}
							}

							if ( ! isSafe ) {
								console.warn(
									'WP Dropzone: Dangerous pattern detected in callback, skipping:',
									eventName,
								);
								continue;
							}

							// Extract and validate function
							try {
								// Use a safer approach: parse the function string
								const funcMatch = fullMatch.match(
									/function\s*\([^)]*\)\s*\{([^}]*)\}/,
								);
								if ( funcMatch ) {
									// Create function using safer method
									// Only allow if it matches the expected pattern
									const funcString = fullMatch.substring(
										fullMatch.indexOf( 'function' ),
									);
									const func = new Function( 'return ' + funcString )();

									// Validate it's actually a function
									if ( typeof func === 'function' ) {
										closure.on( eventName, func );
									} else {
										console.warn(
											'WP Dropzone: Callback did not evaluate to a function:',
											eventName,
										);
									}
								}
							} catch ( e ) {
								console.warn(
									'WP Dropzone: Invalid callback function:',
									eventName,
									e,
								);
							}
						}
					}
				},
				/**
				 * File sending callback
				 *
				 * Adds security nonce and original file type to upload data.
				 * @param {File}           file - The file being uploaded
				 * @param {XMLHttpRequest} xhr  - The XMLHttpRequest object
				 * @param {FormData}       data - The upload data being sent
				 */
				sending( file, xhr, data ) {
					data.append( 'nonce', configData.nonce || '' );
					data.append( 'origtype', file.type );
				},
				/**
				 * Maximum files exceeded callback
				 *
				 * Handles when user tries to upload more files than allowed.
				 * Removes the excess file and shows alert if configured.
				 * @param {File} file - The file that exceeded the limit
				 */
				maxfilesexceeded( file ) {
					this.removeFile( file );

					if ( configData.max_files_alert ) {
						alert( configData.max_files_alert );
					}
				},
				/**
				 * Upload success callback
				 *
				 * Handles successful file uploads by updating the target DOM element
				 * with the uploaded file URL if dom_id is configured.
				 * @param {File}   file     - The successfully uploaded file
				 * @param {Object} response - Server response with upload result
				 */
				success( file, response ) {
					// Parse response if it's a string
					let parsedResponse = response;

					if ( typeof response === 'string' ) {
						try {
							parsedResponse = JSON.parse( response );
						} catch ( e ) {
							console.error( 'WP Dropzone: Failed to parse response', e );
							parsedResponse = response;
						}
					}

					// Handle successful upload
					// WordPress now sends proper HTTP error status codes, so errors go to error callback
					if ( configData.dom_id && configData.dom_id.length > 0 ) {
						if (
							parsedResponse.error == 'false' ||
							parsedResponse.error === false ||
							! parsedResponse.error
						) {
							const targetElement = document.getElementById( configData.dom_id );

							if ( targetElement ) {
								targetElement.value = parsedResponse.data;
							}
						}
					}
				},
				/**
				 * Upload error callback
				 *
				 * Handles upload errors and displays them in the Dropzone UI.
				 * @param {File}           file    - The file that failed to upload
				 * @param {string|Object}  message - Error message or response object
				 * @param {XMLHttpRequest} xhr     - The XMLHttpRequest object
				 */
				error( file, message, xhr ) {
					// Handle WordPress error response format
					// wp_send_json_error() sends {success: false, data: "message"}
					let errorMessage = message;

					// Try to extract error message from XHR response if available
					if ( xhr && xhr.responseText ) {
						try {
							const response = JSON.parse( xhr.responseText );

							if ( response.data ) {
								errorMessage = response.data;
							} else if ( response.error ) {
								errorMessage = response.error;
							}
						} catch ( e ) {
							// Not JSON, use original message
						}
					}

					// Handle if message is an object
					if ( typeof errorMessage === 'object' && errorMessage !== null ) {
						if ( errorMessage.data ) {
							errorMessage = errorMessage.data;
						} else if ( errorMessage.error ) {
							errorMessage = errorMessage.error;
						} else if ( errorMessage.message ) {
							errorMessage = errorMessage.message;
						} else {
							errorMessage = 'Upload failed. Please try again.';
						}
					}

					// Ensure errorMessage is a string
					if ( typeof errorMessage !== 'string' ) {
						errorMessage = String( errorMessage );
					}

					// Manually set error state to ensure Dropzone displays the error
					if ( file.previewElement ) {
						file.previewElement.classList.add( 'dz-error' );
						file.previewElement.classList.remove( 'dz-success', 'dz-complete' );

						// Set error message in preview element
						const errorNodes =
							file.previewElement.querySelectorAll( '[data-dz-errormessage]' );

						errorNodes.forEach( function ( node ) {
							node.textContent = errorMessage;
						} );
					}

					// Set file status to error
					file.status = Dropzone.ERROR;
				},
			} );
		} catch ( error ) {
			/**
			 * Error handling for dropzone initialization
			 *
			 * Logs errors to console if dropzone fails to initialize,
			 * typically due to invalid configuration or missing elements.
			 */
			console.error( 'WP Dropzone: Error initializing dropzone for #' + dropzone.id, error );
		}
	} );
} );
