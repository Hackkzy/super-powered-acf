module.exports = function (grunt) {
  "use strict";

  // Project configuration
  grunt.initConfig({
    pkg: grunt.file.readJSON("package.json"),

    addtextdomain: {
      options: {
        textdomain: "super-powered-acf",
      },
      update_all_domains: {
        options: {
          updateDomains: true,
        },
        src: [
          "*.php",
          "**/*.php",
          "!.git/**/*",
          "!bin/**/*",
          "!node_modules/**/*",
          "!tests/**/*",
        ],
      },
    },

    wp_readme_to_markdown: {
      your_target: {
        files: {
          "README.md": "readme.txt",
        },
      },
    },

    makepot: {
      target: {
        options: {
          domainPath: "/languages",
          exclude: [".git/*", "bin/*", "node_modules/*", "tests/*"],
          mainFile: "super-powered-acf.php",
          potFilename: "super-powered-acf.pot",
          potHeaders: {
            poedit: true,
            "x-poedit-keywordslist": true,
          },
          type: "wp-plugin",
          updateTimestamp: true,
        },
      },
    },

    cssmin: {
      dev_public: {
        files: [
          {
            expand: true,
            cwd: "app/public/assets/css",
            src: ["*.css", "!*.min.css"],
            dest: "app/public/assets/css",
            ext: ".min.css",
          },
        ],
      },
      dev_admin: {
        files: [
          {
            expand: true,
            cwd: "app/admin/assets/css",
            src: ["*.css", "!*.min.css"],
            dest: "app/admin/assets/css",
            ext: ".min.css",
          },
        ],
      },
    },

    uglify: {
      dev_public: {
        files: [
          {
            expand: true,
            src: [
              "app/public/assets/js/*.js",
              "!app/public/assets/js/*.min.js",
            ],
            dest: "app/public/assets/js",
            cwd: ".",
            rename: function (dst, src) {
              return src.replace(".js", ".min.js");
            },
          },
        ],
      },

      dev_admin: {
        files: [
          {
            expand: true,
            src: ["app/admin/assets/js/*.js", "!app/admin/assets/js/*.min.js"],
            dest: "app/admin/assets/js",
            cwd: ".",
            rename: function (dst, src) {
              return src.replace(".js", ".min.js");
            },
          },
        ],
      },
    },

    watch: {
      scripts: {
        files: ["**/*.js", "**/*.css", "**/*.php"],
        tasks: ["addtextdomain", "makepot", "cssmin", "uglify"],
        options: {
          spawn: false,
        },
      },
    },
  });

  // Load tasks.
  grunt.loadNpmTasks("grunt-wp-i18n");
  grunt.loadNpmTasks("grunt-wp-readme-to-markdown");
  grunt.loadNpmTasks("grunt-contrib-watch");
  grunt.loadNpmTasks("grunt-contrib-cssmin");
  grunt.loadNpmTasks("grunt-contrib-uglify");

  // Register tasks.
  grunt.registerTask("default", ["i18n"]);
  grunt.registerTask("i18n", ["addtextdomain", "makepot"]);
  grunt.registerTask("readme", ["wp_readme_to_markdown"]);
  grunt.registerTask("minify", ["cssmin", "uglify"]);

  grunt.util.linefeed = "\n";
};
