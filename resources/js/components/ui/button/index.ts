import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-none text-sm font-mono font-bold disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:ring-primary focus-visible:ring-offset-1 focus-visible:ring-2 transition-all duration-200 shadow-none",
  {
    variants: {
      variant: {
        default:
          "bg-primary text-primary-foreground border-3 border-neo-dark hover:shadow-neo-hard active:shadow-none active:translate-x-[2px] active:translate-y-[2px]",
        destructive:
          "bg-destructive text-destructive-foreground border-3 border-neo-dark hover:shadow-neo-hard active:shadow-none active:translate-x-[2px] active:translate-y-[2px] focus-visible:ring-destructive",
        outline:
          "bg-transparent border-3 border-neo-dark hover:bg-accent hover:text-accent-foreground",
        secondary:
          "bg-black text-white border-3 border-neo-dark hover:shadow-neo-hard active:shadow-none active:translate-x-[2px] active:translate-y-[2px] focus-visible:ring-black",
        ghost:
          "hover:bg-accent hover:text-accent-foreground",
        link: "text-primary underline-offset-4 hover:underline",
      },
      size: {
        "default": "h-10 px-4 py-2 has-[>svg]:px-3",
        "sm": "h-8 gap-1.5 px-3 has-[>svg]:px-2.5",
        "lg": "h-12 px-6 has-[>svg]:px-4 text-base",
        "icon": "size-10",
        "icon-sm": "size-8",
        "icon-lg": "size-12",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
