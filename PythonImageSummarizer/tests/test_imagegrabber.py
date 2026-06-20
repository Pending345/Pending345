import os
import tempfile
import unittest
from PIL import Image

import imagegrabber


class DummyWidget:
    def __init__(self, *args, **kwargs):
        pass

    def pack(self, *args, **kwargs):
        pass

    def destroy(self):
        pass

    def bind(self, *args, **kwargs):
        pass


class DummyLabel(DummyWidget):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.image = None


class DummyButton(DummyWidget):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)


class DummyScale:
    def __init__(self, *args, **kwargs):
        self._v = 1.0

    def set(self, v):
        self._v = v

    def get(self):
        return self._v

    def pack(self, *args, **kwargs):
        pass


class DummyCanvas(DummyWidget):
    def create_window(self, *args, **kwargs):
        pass

    def configure(self, *args, **kwargs):
        pass

    def bbox(self, *args, **kwargs):
        return (0, 0, 0, 0)


class DummyScrollbar(DummyWidget):
    def config(self, *args, **kwargs):
        pass


class DummyTextBox:
    def __init__(self, *args, **kwargs):
        self.content = ""

    def delete(self, start, end):
        self.content = ""

    def insert(self, pos, text):
        self.content += text

    def get(self, *args):
        return self.content

    def pack(self, *args, **kwargs):
        pass


class DummyEntry:
    def __init__(self, *args, **kwargs):
        self._text = ""

    def get(self):
        return self._text

    def set_text(self, text):
        self._text = text

    def pack(self, *args, **kwargs):
        pass


class TestImageGrabberMultipleImages(unittest.TestCase):
    def test_choose_multiple_images_adds_items(self):
        # Create temporary image files with different extensions
        temp_dir = tempfile.mkdtemp()
        exts = ["png", "jpg", "jpeg", "gif", "bmp"]
        fmt_map = {"png": "PNG", "jpg": "JPEG", "jpeg": "JPEG", "gif": "GIF", "bmp": "BMP"}
        paths = []
        for i, ext in enumerate(exts):
            p = os.path.join(temp_dir, f"img_{i}.{ext}")
            Image.new("RGB", (10, 10), color=(i * 10, i * 10, i * 10)).save(p, format=fmt_map[ext])
            paths.append(p)

        # Monkeypatch file dialog to return our files
        imagegrabber.filedialog.askopenfilenames = lambda title, filetypes: tuple(paths)

        # Avoid keeping file handles open by wrapping Image.open to copy and close the original
        original_open = imagegrabber.Image.open

        def open_and_copy(fp, *args, **kwargs):
            im = original_open(fp, *args, **kwargs)
            im_copy = im.copy()
            try:
                im.close()
            except Exception:
                pass
            return im_copy

        imagegrabber.Image.open = open_and_copy

        # Mock ImageTk.PhotoImage so it doesn't require a Tkinter image
        imagegrabber.ImageTk.PhotoImage = lambda img: object()

        # Replace tkinter widget constructors with dummies
        imagegrabber.tk.Frame = lambda *a, **k: DummyWidget()
        imagegrabber.tk.Label = lambda *a, **k: DummyLabel()
        imagegrabber.tk.Button = lambda *a, **k: DummyButton()
        imagegrabber.tk.Scale = lambda *a, **k: DummyScale()
        imagegrabber.tk.Canvas = lambda *a, **k: DummyCanvas()
        imagegrabber.tk.Scrollbar = lambda *a, **k: DummyScrollbar()

        # Ensure container exists
        imagegrabber.images_container = DummyWidget()

        # Clear any previous state
        imagegrabber.image_items.clear()
        imagegrabber.image_refs.clear()

        # Call the function under test
        imagegrabber.choose_images()

        # Verify that all images were added
        self.assertEqual(len(imagegrabber.image_items), len(paths))
        self.assertEqual(len(imagegrabber.image_refs), len(paths))

        # Cleanup
        try:
            for p in paths:
                os.remove(p)
            os.rmdir(temp_dir)
        except Exception:
            pass

    def test_multiple_images_display_simultaneously(self):
        """Verify that multiple images display at the same time with separate UI frames and labels."""
        # Create three temporary image files
        temp_dir = tempfile.mkdtemp()
        paths = []
        for i in range(3):
            p = os.path.join(temp_dir, f"display_img_{i}.png")
            Image.new("RGB", (50, 50), color=(i * 50, i * 50, i * 50)).save(p)
            paths.append(p)

        # Track created frames and labels to verify separate UI elements
        created_frames = []
        created_labels = []

        class TrackingFrame(DummyWidget):
            def __init__(self, *args, **kwargs):
                super().__init__(*args, **kwargs)
                created_frames.append(self)

        class TrackingLabel(DummyLabel):
            def __init__(self, *args, **kwargs):
                super().__init__(*args, **kwargs)
                created_labels.append(self)

        # Monkeypatch file dialog and Image.open
        imagegrabber.filedialog.askopenfilenames = lambda title, filetypes: tuple(paths)

        original_open = imagegrabber.Image.open

        def open_and_copy(fp, *args, **kwargs):
            im = original_open(fp, *args, **kwargs)
            im_copy = im.copy()
            try:
                im.close()
            except Exception:
                pass
            return im_copy

        imagegrabber.Image.open = open_and_copy
        imagegrabber.ImageTk.PhotoImage = lambda img: object()

        # Replace tkinter widgets with tracking versions
        imagegrabber.tk.Frame = lambda *a, **k: TrackingFrame()
        imagegrabber.tk.Label = lambda *a, **k: TrackingLabel()
        imagegrabber.tk.Button = lambda *a, **k: DummyButton()
        imagegrabber.tk.Scale = lambda *a, **k: DummyScale()
        imagegrabber.tk.Canvas = lambda *a, **k: DummyCanvas()
        imagegrabber.tk.Scrollbar = lambda *a, **k: DummyScrollbar()

        # Ensure container exists
        imagegrabber.images_container = DummyWidget()

        # Clear state
        imagegrabber.image_items.clear()
        imagegrabber.image_refs.clear()
        created_frames.clear()
        created_labels.clear()

        # Call function under test
        imagegrabber.choose_images()

        # Verify multiple images displayed with separate UI elements
        self.assertEqual(len(imagegrabber.image_items), 3, "Should have 3 image items")
        self.assertEqual(len(imagegrabber.image_refs), 3, "Should have 3 image refs")
        self.assertGreaterEqual(len(created_frames), 3, "Should create at least 3 frames (one per image)")
        self.assertGreaterEqual(len(created_labels), 3, "Should create at least 3 labels (one per image)")

        # Verify each image item has a frame and photo
        for item in imagegrabber.image_items:
            self.assertIn("frame", item)
            self.assertIn("photo", item)

        # Cleanup
        try:
            for p in paths:
                os.remove(p)
            os.rmdir(temp_dir)
        except Exception:
            pass

    def test_text_limit_validation(self):
        """Verify that text input is limited to 0-2000 characters."""
        # Mock text input and text box
        text_input_mock = DummyEntry()
        text_box_mock = DummyTextBox()

        imagegrabber.text_input = text_input_mock
        imagegrabber.text_box = text_box_mock

        # Test 1: Empty text should not be inserted
        text_input_mock.set_text("")
        imagegrabber.insert_text_into_box()
        self.assertEqual(text_box_mock.content, "", "Empty text should not be inserted")

        # Test 2: Valid text within limit (100 chars)
        valid_text = "x" * 100
        text_input_mock.set_text(valid_text)
        imagegrabber.insert_text_into_box()
        self.assertEqual(text_box_mock.content, valid_text, "Valid text (100 chars) should be inserted")

        # Test 3: Text at the limit (2000 chars)
        limit_text = "y" * 2000
        text_input_mock.set_text(limit_text)
        imagegrabber.insert_text_into_box()
        self.assertEqual(len(text_box_mock.content), 2000, "Text at limit (2000 chars) should be inserted")
        self.assertEqual(text_box_mock.content, limit_text, "Text should match limit exactly")

        # Test 4: Text over limit (2500 chars) should be truncated to 2000
        over_limit_text = "z" * 2500
        text_input_mock.set_text(over_limit_text)
        imagegrabber.insert_text_into_box()
        self.assertEqual(len(text_box_mock.content), 2000, "Text over limit should be truncated to 2000")
        self.assertEqual(text_box_mock.content, "z" * 2000, "Truncated text should match first 2000 chars")

        # Test 5: Text significantly over limit (5000 chars)
        far_over_limit = "a" * 5000
        text_input_mock.set_text(far_over_limit)
        imagegrabber.insert_text_into_box()
        self.assertEqual(len(text_box_mock.content), 2000, "Text far over limit should be truncated to 2000")
        self.assertLessEqual(len(text_box_mock.content), 2000, "Text length should never exceed 2000")


if __name__ == "__main__":
    unittest.main()
