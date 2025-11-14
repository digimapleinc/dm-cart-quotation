jQuery(document).ready(function ($) {
	// When the document is fully loaded, execute the following code.
	const REDIRECT_TIME = 2000;
	// Event listener for the "Send Quote" button with the ID "wcq-create-quotation"
	$("#wcq-create-quotation").on("click", function (e) {
		e.preventDefault(); // Prevent the default form submission behavior.
		showPreloader();
		// Perform an AJAX request to create a quotation link.
		$.ajax({
			url: ajax_object.ajax_url, // The URL for WordPress's admin-ajax.php to handle AJAX requests.
			method: "POST", // The HTTP method used for the request.
			data: {
				action: "create_quotation_link", // The action that will trigger a specific PHP function.
				nonce: ajax_object.create_quotation_nonce, // Security nonce
			},
			success: function (response) {
				// This function executes if the AJAX request is successful.
				if (response) {
					const { data } = response; // Destructure the response data.

					// Copy the generated quotation link to the clipboard.
					navigator.clipboard
						.writeText(data.link)
						.then(() => {
							// On successful copy, display a success notice.
							document.getElementById("copy-status").style.display = "block";
							document.getElementById("copy-status").className =
								"wcq-notice-success";
							document.getElementById("copy-status").textContent = data.message;

							// Hide the notice after 3 seconds.
							setTimeout(() => {
								document.getElementById("copy-status").style.display = "none";
							}, REDIRECT_TIME);
						})
						.catch((err) => {
							// If an error occurs during the copy action, display an error notice.
							document.getElementById("copy-status").className =
								"wcq-notice-error";
							document.getElementById("copy-status").textContent = data.message;
							console.error("Error copying text: ", err); // Log the error to the console.
						});
				} else {
					// If the response is empty or undefined, display an error notice.
					document.getElementById("copy-status").style.display = "block";
					document.getElementById("copy-status").className = "wcq-notice-error";
					document.getElementById("copy-status").textContent =
						"Something went wrong here.";

					// Hide the error notice after 3 seconds.
					setTimeout(() => {
						document.getElementById("copy-status").style.display = "none";
					}, 2000);
				}
				hidePreloader();
			},
			error: function (error) {
				// If the AJAX request fails, log the error to the console.
				console.error("Error:", error);
				hidePreloader();
			},
		});
	});

	// Event listener for the "Empty Cart" button with the ID "wcq-empty-cart"
	$("#wcq-empty-cart").on("click", function (e) {
		e.preventDefault(); // Prevent the default form submission behavior.
		showPreloader();
		// Perform an AJAX request to empty the WooCommerce cart.
		$.ajax({
			url: ajax_object.ajax_url, // The URL for WordPress's admin-ajax.php to handle AJAX requests.
			method: "POST", // The HTTP method used for the request.
			data: {
				action: "empty_cart_quotation", // The action that will trigger a specific PHP function.
				nonce: ajax_object.empty_cart_nonce, // Security nonce
			},
			success: function (response) {
				// This function executes if the AJAX request is successful.
				if (response) {
					const { data } = response; // Destructure the response data.

					if (data.status == "success") {
						// If the cart was successfully emptied, display a success notice.
						document.getElementById("copy-status").style.display = "block";
						document.getElementById("copy-status").className =
							"wcq-notice-success";
						document.getElementById("copy-status").textContent = data.message;

						// Hide the success notice after 3 seconds and redirect to the cart page.
						setTimeout(() => {
							document.getElementById("copy-status").style.display = "none";
							window.location.href = data.redirect; // Redirect to the cart page.
						}, REDIRECT_TIME);
					}
				} else {
					// If the response is empty or undefined, display an error notice.
					document.getElementById("copy-status").style.display = "block";
					document.getElementById("copy-status").className = "wcq-notice-error";
					document.getElementById("copy-status").textContent = data.message;

					// Hide the error notice after 3 seconds.
					setTimeout(() => {
						document.getElementById("copy-status").style.display = "none";
					}, REDIRECT_TIME);
				}
				hidePreloader();
			},
			error: function (error) {
				// If the AJAX request fails, display an error notice and log the error to the console.
				document.getElementById("copy-status").style.display = "block";
				document.getElementById("copy-status").className = "wcq-notice-error";
				document.getElementById("copy-status").textContent =
					"Unknown error occurred.";

				// Hide the error notice after 3 seconds.
				setTimeout(() => {
					document.getElementById("copy-status").style.display = "none";
				}, REDIRECT_TIME);

				console.error("Error:", error); // Log the error to the console.
				hidePreloader();
			},
		});
	});
});
