const publicRoot = 'public/';
export const styles = {
  watch: [`${publicRoot}assets/styles/**/*.scss`],
  globs: [`${publicRoot}assets/styles/**/*.scss`,'!**/_*.scss'],
  output: `${publicRoot}assets/styles/`,
};

