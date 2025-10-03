module.exports = function (context, options) {
  return {
    name: "tailwind-plugin",
    configurePostCss(postcssOptions) {
      postcssOptions.plugins.push(
        require("tailwindcss"),
        require("autoprefixer")
      );
      return postcssOptions;
    },
  };
};
