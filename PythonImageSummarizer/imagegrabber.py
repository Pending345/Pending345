import tkinter as tk
from tkinter import filedialog
from PIL import Image, ImageTk
from airesponse import HuggingFaceResponder

# Globals used by callbacks
image_refs = []
image_items = []
current_image_path = None
text_input = None
text_box = None
responder = HuggingFaceResponder()


def choose_image():
    global current_image_path
    file_path = filedialog.askopenfilename(
        title="Select an image",
        filetypes=[
            ("Image files", "*.png *.jpg *.jpeg *.gif *.bmp"),
            ("All files", "*.*"),
        ],
    )
    if not file_path:
        return

    current_image_path = file_path
    image = Image.open(file_path)
    image.thumbnail((800, 600))
    photo = ImageTk.PhotoImage(image)

    image_label.config(image=photo)
    image_label.image = photo
    image_refs.append(photo)

    generate_summary_button.config(state=tk.NORMAL)


def remove_image(item):
    item["frame"].destroy()
    if item["photo"] in image_refs:
        image_refs.remove(item["photo"])
    if item in image_items:
        image_items.remove(item)


def clear_all_images():
    for item in list(image_items):
        item["frame"].destroy()
    image_items.clear()
    image_refs.clear()


def insert_text_into_box():
    """Insert text into textbox with character limit validation (0-2000 chars)."""
    if not text_input or not text_box:
        return
    
    text = text_input.get()
    if not text:
        return
    
    # Enforce text limit: 0-2000 characters
    if len(text) > 2000:
        text = text[:2000]
    
    text_box.delete("1.0", tk.END)
    text_box.insert(tk.END, text)


def choose_images():
    file_paths = filedialog.askopenfilenames(
        title="Select images",
        filetypes=[
            ("Image files", "*.png *.jpg *.jpeg *.gif *.bmp"),
            ("All files", "*.*"),
        ],
    )
    if not file_paths:
        return

    for file_path in file_paths:
        image = Image.open(file_path)
        image.thumbnail((400, 300))
        photo = ImageTk.PhotoImage(image)

        item_frame = tk.Frame(images_container, bd=1, relief=tk.RIDGE)
        label = tk.Label(item_frame, image=photo)
        label.image = photo
        label.pack(padx=5, pady=5)

        remove_button = tk.Button(
            item_frame,
            text="Remove",
            command=lambda item={"frame": item_frame, "photo": photo}: remove_image(item),
        )
        remove_button.pack(padx=5, pady=(0, 5))

        item_frame.pack(side=tk.LEFT, padx=5, pady=5)

        image_items.append({"frame": item_frame, "photo": photo})
        image_refs.append(photo)


def generate_summary():
    global current_image_path
    if not current_image_path:
        return

    text_box.delete("1.0", tk.END)
    text_box.insert(tk.END, "Generating summary...")
    root.update()

    try:
        # sampling params from UI
        try:
            temp = float(temperature_scale.get())
        except Exception:
            temp = 1.0
        try:
            tp = float(top_p_scale.get())
        except Exception:
            tp = 0.9
        try:
            num = int(num_samples_scale.get())
        except Exception:
            num = 1

        summary = responder.summarize_image(
            current_image_path,
            do_sample=True,
            temperature=temp,
            top_p=tp,
            num_return_sequences=num,
        )

        # enforce character limit from UI control if present
        try:
            char_limit = int(char_limit_scale.get())
            if char_limit > 0 and len(summary) > char_limit:
                summary = summary[:char_limit].rstrip()
        except Exception:
            pass

        text_box.delete("1.0", tk.END)
        text_box.insert(tk.END, summary)
    except Exception as e:
        text_box.delete("1.0", tk.END)
        text_box.insert(tk.END, f"Error: {str(e)}")


def main():
    global images_canvas, images_container, root
    global button_single, button_multiple, remove_all_button, generate_summary_button
    global temp_label, temperature_scale, top_p_label, top_p_scale
    global char_limit_label, char_limit_scale, num_samples_scale
    global image_label, text_label, text_input, insert_button, text_box, scrollbar

    root = tk.Tk()
    root.title("Image Grabber")

    main_frame = tk.Frame(root)
    main_frame.pack(fill=tk.BOTH, expand=True)

    left_frame = tk.Frame(main_frame)
    left_frame.pack(side=tk.LEFT, fill=tk.BOTH, expand=False, padx=10, pady=10)

    right_frame = tk.Frame(main_frame)
    right_frame.pack(side=tk.LEFT, fill=tk.BOTH, expand=True, padx=10, pady=10)

    image_frame = tk.LabelFrame(left_frame, text="Selected Image", width=500, height=350)
    image_frame.pack(fill=tk.BOTH, expand=True)
    image_frame.pack_propagate(False)

    image_label = tk.Label(image_frame)
    image_label.pack(fill=tk.BOTH, expand=True)

    controls_frame = tk.LabelFrame(left_frame, text="Controls")
    controls_frame.pack(fill=tk.X, pady=(10, 0))

    button_frame = tk.Frame(controls_frame)
    button_frame.pack(fill=tk.X, pady=5)

    button_single = tk.Button(button_frame, text="Choose Image", command=choose_image)
    button_single.pack(side=tk.LEFT, padx=2)

    button_multiple = tk.Button(button_frame, text="Choose Multiple Images", command=choose_images)
    button_multiple.pack(side=tk.LEFT, padx=2)

    remove_all_button = tk.Button(button_frame, text="Remove All Images", command=clear_all_images)
    remove_all_button.pack(side=tk.LEFT, padx=2)

    generate_summary_button = tk.Button(button_frame, text="Generate Summary", command=generate_summary, state=tk.DISABLED)
    generate_summary_button.pack(side=tk.LEFT, padx=2)

    # Sampling controls to allow varied summaries
    temp_label = tk.Label(controls_frame, text="Temperature:")
    temp_label.pack(anchor="w", padx=5, pady=(5, 0))
    temperature_scale = tk.Scale(controls_frame, from_=0.0, to=1.5, resolution=0.1, orient=tk.HORIZONTAL)
    temperature_scale.set(1.0)
    temperature_scale.pack(fill=tk.X, padx=5, pady=(0, 5))

    top_p_label = tk.Label(controls_frame, text="Top-p:")
    top_p_label.pack(anchor="w", padx=5, pady=(5, 0))
    top_p_scale = tk.Scale(controls_frame, from_=0.0, to=1.0, resolution=0.05, orient=tk.HORIZONTAL)
    top_p_scale.set(0.9)
    top_p_scale.pack(fill=tk.X, padx=5, pady=(0, 5))

    # Num samples control
    num_label = tk.Label(controls_frame, text="Num samples:")
    num_label.pack(anchor="w", padx=5, pady=(5, 0))
    num_samples_scale = tk.Scale(controls_frame, from_=1, to=5, resolution=1, orient=tk.HORIZONTAL)
    num_samples_scale.set(1)
    num_samples_scale.pack(fill=tk.X, padx=5, pady=(0, 5))

    # Character limit for displayed summary (characters)
    char_limit_label = tk.Label(controls_frame, text="Summary char limit:")
    char_limit_label.pack(anchor="w", padx=5, pady=(5, 0))
    char_limit_scale = tk.Scale(controls_frame, from_=50, to=2000, resolution=10, orient=tk.HORIZONTAL)
    char_limit_scale.set(500)
    char_limit_scale.pack(fill=tk.X, padx=5, pady=(0, 5))

    global text_input, text_box
    
    text_label = tk.Label(right_frame, text="Enter text:")
    text_label.pack(padx=10, pady=(0, 5), anchor="w")

    text_input = tk.Entry(right_frame, width=50)
    text_input.pack(padx=10, pady=(0, 5), fill=tk.X)

    insert_button = tk.Button(right_frame, text="Insert into Textbox", command=insert_text_into_box)
    insert_button.pack(padx=10, pady=(0, 10), anchor="w")

    text_box = tk.Text(right_frame, height=20, width=60)
    text_box.pack(padx=10, pady=(0, 10), fill=tk.BOTH, expand=True)

    scrollbar = tk.Scrollbar(right_frame, orient=tk.HORIZONTAL, command=text_box.xview)
    scrollbar.pack(fill=tk.X, side=tk.BOTTOM)
    text_box.configure(xscrollcommand=scrollbar.set)

    thumbnails_frame = tk.LabelFrame(left_frame, text="Thumbnails")
    thumbnails_frame.pack(fill=tk.BOTH, pady=(10, 0), expand=False)

    images_canvas = tk.Canvas(thumbnails_frame, height=150, bg="#f0f0f0")
    images_canvas.pack(fill=tk.BOTH, side=tk.LEFT, expand=True)

    thumbnails_scrollbar = tk.Scrollbar(thumbnails_frame, orient=tk.HORIZONTAL, command=images_canvas.xview)
    thumbnails_scrollbar.pack(fill=tk.X, side=tk.BOTTOM)
    images_canvas.configure(xscrollcommand=thumbnails_scrollbar.set)

    images_container = tk.Frame(images_canvas)
    images_canvas.create_window((0, 0), window=images_container, anchor="nw")

    def on_frame_configure(event):
        images_canvas.configure(scrollregion=images_canvas.bbox("all"))

    images_container.bind("<Configure>", on_frame_configure)

    root.mainloop()


if __name__ == "__main__":
    main()
