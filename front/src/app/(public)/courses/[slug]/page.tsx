import Link from "next/link";
import Button from "@/components/Button";
import Image from "next/image";

const CourseDetails = () => {
    return (
        <div className={"text-base leading-8"}>
            <div className={"w-full aspect-video relative rounded shadow-lg mb-20"}>
                <Image
                    src={'http://localhost:3000/assets/images/placeholder-image.png'}
                    alt={'university'}
                    fill={true}
                    loading={"lazy"}
                    decoding={"async"}
                    sizes={"100vw"}
                    className={"object-cover rounded shadow-lg w-full h-full"}
                />
            </div>
            <div className={"mb-20"}>
                <h1 className={"text-2xl font-bold mb-5"}>آموزش پیشرفته برنامه نویسی پایتون</h1>
                <p className={"text-justify"}>
                    لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.
                </p>
            </div>
            <div className={"grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 auto-rows-fr gap-12 mb-20"}>
                <div>
                    <h4 className={"text-2xl font-bold mb-5"}>سرفصل ها</h4>
                    <ul className={"list-decimal pr-5"}>
                        <li>oop</li>
                        <li>solid</li>
                        <li>clean code</li>
                        <li>loop</li>
                        <li>functions</li>
                    </ul>
                </div>
                <div>
                    <h3 className={"text-2xl font-bold mb-5"}>پیش نیاز</h3>
                    <ul className={"list-decimal pr-5"}>
                        <li>مبانی برنامه نویسی</li>
                        <li>مبانی پایتون</li>
                    </ul>
                </div>
                <div>
                    <h3 className={"text-2xl font-bold mb-5"}>استاد مدرس</h3>
                    <h2 className={"text-lg text-cyan-700"}>
                        <Link href={"#"}>امیرحسین حسینخانی</Link>
                    </h2>
                </div>
                <div>
                    <h3 className={"text-2xl font-bold mb-5"}>روز و ساعت برگزاری</h3>
                    <p>یک شنبه ها از ساعت 11:00 تا 13:00</p>
                </div>
            </div>
            <div className={"flex flex-row justify-center items-center mb-20"}>
                <div>
                    <Button
                        type={'link'}
                        variant={'primary'}
                        style={'fill'}
                        href={"#"}
                        onClick={() => {}}
                        disabled={false}
                        className={'py-3 px-10 rounded shadow-lg block w-fit'}
                    >
                        ثبت نام کنید
                    </Button>
                </div>
            </div>
        </div>
    );
};

export default CourseDetails;