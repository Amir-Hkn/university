import Image from "next/image";
import Button from "@/components/Button";

type CardProps = {
    className?: string;
};

const Card = ({ className = "" }: CardProps) => {
    return (
        <article
            className={`rounded shadow-lg overflow-hidden relative z-0 ${className}`}
        >
            <Image
                src={'http://localhost:3000/assets/images/placeholder-image.png'}
                alt={'test'}
                fill={true}
                decoding={'async'}
                loading={"lazy"}
                sizes={"100vw"}
                className={"object-cover z-0"}
            />
            <div className={"absolute z-1 bottom-0 min-h-1/2 px-4 py-6"}>
                <div className={"absolute -z-1 blur-xs bg-linear-to-t from-[#397BFA] to-[#397BFA]/2 w-full h-full top-0 left-0"}></div>
                <h2 className={"text-primary text-xl font-bold mb-5"}>عنوان کارت</h2>
                <p className={"line-clamp-3 text-sm text-justify mb-5 leading-8"}>
                    لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز
                </p>
                <Button
                    type={'link'}
                    variant={'primary'}
                    style={'outline'}
                    href={'#'}
                    onClick={() => {}}
                    disabled={false}
                    className={'text-sm py-2 mx-auto rounded block text-center'}
                >بیشتر</Button>
            </div>
        </article>
    );
};

export default Card;