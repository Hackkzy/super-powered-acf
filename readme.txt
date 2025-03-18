=== Super Powered ACF ===
Contributors: hackkzy404
Tags: acf, advanced-custom-fields, ai-field-generator, automation, wordpress-ai
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate ACF fields automatically with AI, saving time and effort when creating new field groups.

== Description ==
🚀 **Super Powered ACF revolutionizes field creation in ACF!**

Instead of manually configuring fields, just **describe what you need**, and AI will generate ACF fields for you!

🔹 **Key Features:**

- **AI-powered field generation** – No manual setup
- **Describe your fields** – AI understands and generates them for you
- **Intelligent field type selection** – AI picks the best match
- **Works with both ACF Free & Pro**

🎯 **How It Works:**  

1. Create a new ACF Field Group
2. Click "Generate Fields with AI"
3. Enter your field requirements
4. AI generates the fields automatically
5. Review, adjust if needed, and save 🎉

== External Services ==
This plugin connects to Google's Gemini API to generate ACF fields based on user prompts.

- **What data is sent?** The user’s entered prompt is sent to the API to generate relevant fields.
- **When is data sent?** Only when the "Generate with AI" button is clicked.
- **Where is data sent?** Requests are sent to Google’s Gemini API endpoint.
- **Privacy Policy:** [Google Privacy Policy](https://policies.google.com/privacy)
- **Terms of Service:** [Google AI Terms](https://policies.google.com/terms)

No personal data, user credentials, or sensitive information is sent.

== Git Repository ==
You can find the source code, report issues, and contribute at:

📌 GitHub Repository: [https://github.com/Hackkzy/super-powered-acf](https://github.com/Hackkzy/super-powered-acf)

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/super-powered-acf` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the "Plugins" screen in WordPress
3. Make sure you have Advanced Custom Fields / Pro installed and activated
4. Start creating a new Field Group to use the AI-powered field generation

== Frequently Asked Questions ==
= Does this work with existing field groups? =

No, AI-powered field generation is only available when creating new field groups. Future updates may extend functionality.

= Do I need Advanced Custom Fields Pro? =

No, this plugin works with both ACF Free and ACF Pro.

= How accurate is the AI-generated output? =

AI generates fields based on your description, but it's always recommended to review and adjust them as needed.

= How do I get a Gemini API Key? =

1. Visit [Google AI's official site](https://ai.google.dev/) and log in with your Google account.  
2. Go to **API Keys** → Click **Generate API Key**  
3. Copy & paste it into **Super Powered ACF** settings in WordPress.

== Changelog ==
= 1.0.0 =
* Initial release