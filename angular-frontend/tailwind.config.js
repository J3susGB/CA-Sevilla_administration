/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
      "./src/**/*.{html,ts}",
    ],
    theme: {
      extend: {
        fontFamily: {
          montserrat: ["Montserrat", "sans-serif"],
          logo: ['Square Peg', 'cursive'],
        },
        colors: {
          logo: "#D1D5DB",
          letra: "#3B3B3B",
          bordes: "#3a6d6e",
        },
        fontSize: {
          xs2: "0.65rem", // Extra pequeño
          mediana: "1.125rem", // entre base y lg
          logo_letra: "2.75rem", // títulos o logo
        },
      },
    },
    plugins: [],
  }
  