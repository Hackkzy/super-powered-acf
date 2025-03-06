(function ($) {
	acf.addAction("ready", function () {
		// Create modal structure using jQuery
		function createModal() {
			const $modal = $("<div>", {
				id: "sup-acf-modal",
				class: "sup-acf-modal",
				role: "dialog",
				"aria-labelledby": "sup-acf-modal-title",
				"aria-modal": "true",
			});

			// Create header
			const $header = $("<div>", { class: "sup-acf-modal-header" })
				.append(
					$("<h2>", {
						id: "sup-acf-modal-title",
						class: "sup-acf-modal-title",
						text: sup_acf.i18n.modalTitle,
					})
				)
				.append(
					$("<button>", {
						id: "sup-acf-generate-cancel",
						class: "sup-acf-close-button",
						"aria-label": "Close modal",
					}).append(
						$("<span>", {
							"aria-hidden": "true",
							text: "×",
						})
					)
				);

			// Create content
			const $content = $("<div>", { class: "sup-acf-modal-content" })
				.append(
					$("<label>", {
						for: "sup-acf-prompt",
						text: sup_acf.i18n.promptLabel,
					})
				)
				.append(
					$("<textarea>", {
						id: "sup-acf-prompt",
						class: "sup-acf-textarea",
						placeholder: sup_acf.i18n.promptPlaceholder,
						"aria-describedby":
							"sup-acf-prompt-help sup-acf-ai-note",
					})
				)
				.append(
					$("<p>", {
						id: "sup-acf-prompt-help",
						class: "sup-acf-help-text",
						text: sup_acf.i18n.promptHelp,
					})
				)
				.append(
					$("<p>", {
						id: "sup-acf-ai-note",
						class: "sup-acf-note",
					})
						.append(
							$("<span>", {
								class: "dashicons dashicons-info",
							})
						)
						.append(sup_acf.i18n.aiNote)
				);

			// Create actions
			const $actions = $("<div>", {
				class: "sup-acf-modal-actions",
			}).append(
				$("<button>", {
					id: "sup-acf-generate-confirm",
					class: "acf-btn acf-btn-primary sup-acf-modal-button",
					text: sup_acf.i18n.generateFields,
				})
			);

			// Loading section
			const $loading = $("<div>", {
				id: "sup-acf-loading",
				class: "sup-acf-loading",
				style: "display:none;",
			})
				.append(
					$("<div>", {
						class: "sup-acf-spinner",
						role: "status",
						"aria-label": sup_acf.i18n.generatingFields,
					})
				)
				.append($("<p>", { text: sup_acf.i18n.generatingFields }));

			// Create error section
			const $error = $("<div>", {
				id: "sup-acf-error",
				class: "sup-acf-error",
				style: "display:none;",
				role: "alert",
			});

			// Assemble modal
			$modal
				.append($header)
				.append(
					$content.append($actions).append($loading).append($error)
				);

			// Create overlay
			const $overlay = $("<div>", {
				id: "sup-acf-modal-overlay",
				class: "sup-acf-modal-overlay",
			});

			return { $modal, $overlay };
		}

		// Create and append the modal elements
		const { $modal, $overlay } = createModal();
		$("body").append($modal).append($overlay);

		// Create and append the generate button
		const $button = $("<button>", {
			id: "sup-acf-generate-fields-btn",
			class: "acf-btn acf-btn-secondary",
			text: sup_acf.i18n.generateWithAI,
		});
		$(".acf-headerbar-actions").append($button);

		// Event listeners
		$(document).on("click", "#sup-acf-generate-fields-btn", showModal);
		$(document).on("click", "#sup-acf-generate-cancel", hideModal);
		$(document).on(
			"click",
			"#sup-acf-generate-confirm",
			handleGenerateFields
		);
		$(document).on("keydown", handleEscapeKey);

		// Handle escape key
		function handleEscapeKey(e) {
			if (e.key === "Escape" && $("#sup-acf-modal").is(":visible")) {
				hideModal();
			}
		}

		function showModal() {
			const $modal = $("#sup-acf-modal");
			const $overlay = $("#sup-acf-modal-overlay");

			$modal.show();
			$overlay.show();

			// Trigger reflow to enable transitions
			$modal[0].offsetHeight;
			$overlay[0].offsetHeight;

			$modal.addClass("is-visible");
			$overlay.addClass("is-visible");

			$("#sup-acf-prompt").focus();
			$("#sup-acf-error").hide();

			// Trap focus within modal
			trapFocus($modal);
		}

		function hideModal() {
			const $modal = $("#sup-acf-modal");
			const $overlay = $("#sup-acf-modal-overlay");

			$modal.removeClass("is-visible");
			$overlay.removeClass("is-visible");

			// Wait for transition to complete before hiding
			setTimeout(() => {
				$modal.hide();
				$overlay.hide();
				$("#sup-acf-prompt").val("");
				$("#sup-acf-error").hide();
				$("#sup-acf-generate-fields-btn").focus();
			}, 300);
		}

		function trapFocus($modal) {
			const focusableElements = $modal
				.find(
					'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
				)
				.toArray();

			const firstFocusable = focusableElements[0];
			const lastFocusable =
				focusableElements[focusableElements.length - 1];

			$modal.on("keydown", function (e) {
				if (e.key === "Tab") {
					if (e.shiftKey) {
						if (document.activeElement === firstFocusable) {
							e.preventDefault();
							lastFocusable.focus();
						}
					} else {
						if (document.activeElement === lastFocusable) {
							e.preventDefault();
							firstFocusable.focus();
						}
					}
				}
			});
		}

		function showError(message) {
			const $error = $("#sup-acf-error");
			$error.html(message).hide().fadeIn(300);
			$("#sup-acf-loading").hide();
			$(".sup-acf-modal-button").prop("disabled", false);

			// Announce error to screen readers
			$error.attr("role", "alert");
		}

		function handleGenerateFields() {
			const $prompt = $("#sup-acf-prompt");
			const prompt = $prompt.val().trim();

			if (!prompt) {
				$prompt.focus();
				showError(sup_acf.i18n.enterPrompt);
				return;
			}

			$("#sup-acf-error").hide();
			$("#sup-acf-loading").hide().fadeIn(300);
			$(".sup-acf-modal-button").prop("disabled", true);

			generateFieldsFromAI(prompt);
		}

		function renderFields($list) {
			const fields = acf.getFieldObjects({ list: $list });

			if (!fields.length) {
				$list
					.addClass("-empty")
					.parents(".acf-field-list-wrap")
					.first()
					.addClass("-empty");
			} else {
				$list
					.removeClass("-empty")
					.parents(".acf-field-list-wrap")
					.first()
					.removeClass("-empty");
				fields.forEach((field, i) => field.prop("menu_order", i));
			}
		}

		function addField(fieldData) {
			// Ensure fieldData exists and is an object.
			if (!fieldData || typeof fieldData !== "object") {
				return;
			}

			// Ensure that 'label' and 'type' exist.
			if (
				!fieldData.label ||
				typeof fieldData.label !== "string" ||
				!fieldData.type ||
				typeof fieldData.type !== "string"
			) {
				return;
			}

			const $fieldList = $(".acf-field-list:first");

			// Get the ACF field template
			const $fieldTemplate = $("#tmpl-acf-field");
			if (!$fieldTemplate.length) {
				console.error("ACF field template not found.");
				return;
			}

			// Create a new jQuery object from the template HTML
			const fieldHtml = $fieldTemplate.html();
			const $fieldElement = $(fieldHtml);

			// Get the placeholder ID from the template (to be replaced)
			const placeholderId = $fieldElement.data("id");

			// Generate a unique field key
			const newFieldKey = acf.uniqid("field_");

			// Duplicate the field template and replace the placeholder ID with the new key
			const $newFieldElement = acf.duplicate({
				target: $fieldElement,
				search: placeholderId,
				replace: newFieldKey,
				append: ($original, $duplicate) =>
					$fieldList.append($duplicate), // Append the new field to the list
			});

			// Get the ACF field object (JavaScript representation of the new field)
			const fieldObject = acf.getFieldObject($newFieldElement);

			// Set essential properties for the field
			fieldObject.prop("key", newFieldKey);
			fieldObject.prop("ID", 0);
			fieldObject.prop("label", fieldData.label);
			fieldObject.prop("name", fieldData.name || newFieldKey);
			fieldObject.prop("instructions", fieldData.instructions || "");

			// Update the new field element attributes for correct identification
			$newFieldElement
				.attr("data-key", newFieldKey)
				.attr("data-id", newFieldKey);

			// Set label and name input values
			$newFieldElement
				.find(".acf-field-label input")
				.val(fieldData.label);
			$newFieldElement
				.find(".acf-field-name input")
				.val(fieldData.name || newFieldKey);

			// Set field type and trigger change event to update ACF settings
			const $fieldTypeDropdown = $newFieldElement.find(".field-type");
			$fieldTypeDropdown.val(fieldData.type).trigger("change");

			// Wait for ACF to apply the field type, then configure additional settings
			setTimeout(() => {
				// If the field type is "select" and has choices, format and apply them
				if (fieldData.type === "select" && fieldData.choices) {
					const $choicesTextarea = $newFieldElement.find(
						".acf-field-setting-choices textarea"
					);
					if ($choicesTextarea.length) {
						// Convert choices object to ACF format (each choice on a new line)
						const formattedChoices = Object.entries(
							fieldData.choices
						)
							.map(([value, label]) => `${value} : ${label}`)
							.join("\n");
						$choicesTextarea.val(formattedChoices).trigger("input");
					}
				}

				// Set the "required" checkbox if necessary
				if (fieldData.required) {
					$newFieldElement
						.find(".acf-field-setting-required input")
						.prop("checked", true)
						.trigger("change");
				}
			}, 1500); // Delay ensures ACF settings are applied properly
			
			// Re-render fields to reflect changes
			renderFields($fieldList);
		}

		function generateFieldsFromAI(prompt) {
			$.ajax({
				url: sup_acf.root + "sup-acf/v1/generate-fields",
				type: "POST",
				contentType: "application/json",
				beforeSend: function (xhr) {
					xhr.setRequestHeader("X-WP-Nonce", sup_acf.nonce);
				},
				data: JSON.stringify({ prompt: prompt }),
				success: function (response) {
					$("#sup-acf-loading").hide();
					$(".sup-acf-modal-button").prop("disabled", false);

					if (
						response.success &&
						response.fields &&
						response.fields.length
					) {
						response.fields.forEach(addField);
						hideModal();
					} else {
						showError(sup_acf.i18n.noFieldsGenerated);
					}
				},
				error: function (jqXHR) {
					const response = jqXHR.responseJSON;
					const errorMessage =
						response && response.message
							? response.message
							: sup_acf.i18n.genericError;
					showError(errorMessage);
				},
			});
		}
	});
})(jQuery);
