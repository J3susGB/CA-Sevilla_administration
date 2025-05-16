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
          bordes_hover: "#609596",
        },
        fontSize: {
          xs2: "0.65rem", // Extra pequeño
          xs3: "0.80rem",
          mediana: "1.125rem", // entre base y lg
          logo_letra: "2.75rem", // títulos o logo
        },
        keyframes: {
          pulse2: {
            '0%': { boxShadow: '0 0 0 0 rgba(30, 27, 75, 0.5)', borderRadius: '9999px' },
            '70%': { boxShadow: '0 0 0 0.7rem rgba(30, 27, 75, 0)', borderRadius: '9999px' },
            '100%': { boxShadow: '0 0 0 0 rgba(30, 27, 75, 0)', borderRadius: '9999px' },
          },
        },
        animation: {
          pulse2: 'pulse2 1s infinite',
        },
      },
    },
    plugins: [],
  }
  