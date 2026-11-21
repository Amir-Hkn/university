"use client";
import Link from "next/link";
import {usePathname} from "next/navigation";

const DesktopMenu = () => {
    const pathname = usePathname();
    const currentPathName = pathname.split("/");

    return (
        <div className={"hidden lg:block w-[calc(100%-400px)]"}>
            <ul className={"flex flex-row items-center gap-14"}>
                <li>
                    <Link href={"/"} className={`text-base ${currentPathName[1] === "" ? 'text-secondary' : 'text-primary'} transition-all duration-300 ease-in-out hover:text-blue-300`}>صفحه اصلی</Link>
                </li>
                <li>
                    <Link href={"/courses"} className={`text-base ${currentPathName[1] === 'courses' ? 'text-secondary' : 'text-primary'} transition-all duration-300 ease-in-out hover:text-blue-300`}>کلاس ها و دوره ها</Link>
                </li>
                <li>
                    <Link href={"/events"} className={`text-base ${currentPathName[1] === 'events' ? 'text-secondary' : 'text-primary'} transition-all duration-300 ease-in-out hover:text-blue-300`}>رویدادها</Link>
                </li>
                <li>
                    <Link href={"/articles"} className={`text-base ${currentPathName[1] === 'articles' ? 'text-secondary' : 'text-primary'} transition-all duration-300 ease-in-out hover:text-blue-300`}>مقالات</Link>
                </li>
                <li>
                    <Link href={"#"} className={"text-base transition-all duration-300 ease-in-out hover:text-blue-300"}>تماس</Link>
                </li>
            </ul>
        </div>
    );
};

export default DesktopMenu;