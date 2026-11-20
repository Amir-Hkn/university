"use client";
import {Menu} from "lucide-react";

const MobileMenu = () => {
    return (
        <div className={"block lg:hidden"}>
            <Menu size={24} />
        </div>
    );
};

export default MobileMenu;