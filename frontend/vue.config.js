const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: "/",
  devServer: {
    historyApiFallback: true,
    client: {
      overlay: {
        runtimeErrors: (error) => {
          // Harmless browser warning from ResizeObserver-based components
          // (e.g. simplebar) during rapid layout changes like breakpoint/
          // sidebar toggles. Doesn't indicate a real error.
          if (error.message === 'ResizeObserver loop completed with undelivered notifications.') {
            return false;
          }
          return true;
        },
      },
    },
  },
  chainWebpack: (config) => {
    config.plugin('define').tap((definitions) => {
      Object.assign(definitions[0], {
        __VUE_OPTIONS_API__: 'true',
        __VUE_PROD_DEVTOOLS__: 'false',
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: 'false'
      })
      return definitions
    })
  }
})
