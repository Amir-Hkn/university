import type { Metadata } from "next";
import "../globals.css";

import {yekan} from "@/utils/fonts";

export const metadata: Metadata = {
  title: "University",
  description: "Starting...",
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html
      lang="fa"
      dir={"rtl"}
      data-app="university-frontend"
      className={`${yekan.className} h-full antialiased`}
    >
      <body className="min-h-full flex flex-col">
      {children}
      <div id={"__modal"}></div>
      </body>
    </html>
  );
}
