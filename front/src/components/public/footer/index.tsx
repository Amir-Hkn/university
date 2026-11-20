import Image from "next/image";
import logo from "@/assets/images/logo.png";
import Link from "next/link";
import {ChevronLeft} from "lucide-react";

const Footer = () => {
    return (
        <footer className={"shadow-lg bg-white border-t border-gray-100"}>
            <div className={"container mx-auto p-4 pt-10"}>
                <div className={"grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 auto-rows-fr gap-10 mb-5"}>
                    <div>
                        <div className={"mb-10"}>
                            <Link href={"/"}>
                                <Image
                                    src={logo}
                                    width={250}
                                    height={140.62}
                                    placeholder={"blur"}
                                    alt={"university"}
                                />
                            </Link>
                        </div>
                        <div>
                            <p className={"text-base text-justify"}>
                                لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز
                            </p>
                        </div>
                    </div>
                    <div>
                        <p className={"text-lg font-bold mb-10"}>منوی سایت</p>
                        <ul>
                            <li className={"mb-5 flex flex-row items-center gap-1"}>
                                <ChevronLeft size={16} />
                                <Link href={"#"} className={"text-base"}>صفحه اصلی</Link>
                            </li>
                            <li className={"mb-5 flex flex-row items-center gap-1"}>
                                <ChevronLeft size={16} />
                                <Link href={"#"} className={"text-base"}>کلاس ها و دوره ها</Link>
                            </li>
                            <li className={"mb-5 flex flex-row items-center gap-1"}>
                                <ChevronLeft size={16} />
                                <Link href={"#"} className={"text-base"}>رویدادها</Link>
                            </li>
                            <li className={"mb-5 flex flex-row items-center gap-1"}>
                                <ChevronLeft size={16} />
                                <Link href={"#"} className={"text-base"}>مقالات</Link>
                            </li>
                            <li className={"flex flex-row items-center gap-1"}>
                                <ChevronLeft size={16} />
                                <Link href={"#"} className={"text-base"}>تماس</Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p className={"text-lg font-bold mb-10"}>حساب کاربری</p>
                        <ul>
                            <li className={"mb-5 flex flex-row items-center gap-1"}>
                                <ChevronLeft size={16} />
                                <Link href={"#"} className={"text-base"}>ثبت نام/ورود</Link>
                            </li>
                            <li className={"mb-5 flex flex-row items-center gap-1"}>
                                <ChevronLeft size={16} />
                                <Link href={"#"} className={"text-base"}>حساب کاربری</Link>
                            </li>
                            <li className={"mb-5 flex flex-row items-center gap-1"}>
                                <ChevronLeft size={16} />
                                <Link href={"#"} className={"text-base"}>کلاس های من</Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p className={"text-lg font-bold mb-10"}>شبکه های مجازی</p>
                        <ul>
                            <li className={"mb-5 flex flex-row items-center gap-1"}>
                                <ChevronLeft size={16} />
                                <Link href={"#"} className={"text-base"}>اینستاگرام</Link>
                            </li>
                            <li className={"mb-5 flex flex-row items-center gap-1"}>
                                <ChevronLeft size={16} />
                                <Link href={"#"} className={"text-base"}>تلگرام</Link>
                            </li>
                            <li className={"mb-5 flex flex-row items-center gap-1"}>
                                <ChevronLeft size={16} />
                                <Link href={"#"} className={"text-base"}>X</Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    );
};

export default Footer;