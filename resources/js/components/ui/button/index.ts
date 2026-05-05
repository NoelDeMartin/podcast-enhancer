import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-none text-sm font-mono font-bold disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:ring-neo-pink focus-visible:ring-2",
  {
    variants: {
      variant: {
        default:
          "bg-neo-pink text-white border-3 border-neo-dark  hover:bg-neo-pink/90 active:bg-neo-pink/80",
        destructive:
          "bg-destructive text-white border-3 border-neo-dark hover:-translate-y-0.5 active: active:translate-x-[4px] active:translate-y-[4px]",
        outline:
          "bg-background border-3 border-neo-dark hover:-translate-y-0.5 active: active:translate-x-[4px] active:translate-y-[4px]",
        secondary:
          "bg-transparent text-primary-foreground border-3 border-neo-dark  transition-none hover:bg-neo-pink hover:border-neo-pink hover:text-white focus-visible:border-neo-pink focus-visible:text-neo-pink focus-visible:hover:text-white focus-visible:ring-0 focus-visible:ring-offset-0 focus-visible:outline-none",
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
