import localFont from "next/font/local";

const yekan = localFont({
    src: [
        {
            path: '../assets/fonts/YekanBakh-Regular.woff2',
            weight: '400',
            style: 'normal'
        }
    ]
});

export {yekan};