import Link from "next/link";
import Image from "next/image";
import {User, Search} from "lucide-react";
import Navigation from "@/components/public/header/Navigation";
import logo from "@/assets/images/logo.png";

const Header = () => {
    return (
        <header className={"shadow-lg sticky z-10 top-0 bg-white"}>
            <div className={"container mx-auto p-4"}>
                <div className={"flex flex-row flex-wrap justify-between items-center"}>
                    {/*  Logo section  */}
                    <div className={"w-25 aspect-video relative"}>
                        <Link href={"/"}>
                            <Image
                                src={logo}
                                width={100}
                                height={56.25}
                                placeholder={"blur"}
                                alt={"university"}
                            />
                        </Link>
                    </div>
                    {/*  navigation section desktop/mobile  */}
                    <Navigation />
                    {/*  user account section/search  */}
                    <div className={"w-50 hidden lg:block"}>
                        <div className={"flex flex-row items-center"}>
                            <div className={"w-1/2"}>
                                <span className={"cursor-pointer flex justify-end"}>
                                    <Search size={20} />
                                </span>
                            </div>
                            <div className={"w-1/2"}>
                                <Link href={"#"} className={"flex justify-end"}>
                                    <User size={20} />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    );
};

export default Header;