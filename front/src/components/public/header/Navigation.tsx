import DesktopMenu from "@/components/public/header/DesktopMenu";
import MobileMenu from "@/components/public/header/MobileMenu";
import {Suspense} from "react";

const Navigation = () => {
    return (
        <>
            <Suspense fallback={<p>loading...</p>}>
                <DesktopMenu />
            </Suspense>
            <MobileMenu />
        </>
    );
};

export default Navigation;