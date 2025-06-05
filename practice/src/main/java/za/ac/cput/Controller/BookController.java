package za.ac.cput.Controller;


import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;
import za.ac.cput.Domain.Book;
import za.ac.cput.Repository.AuthorRepository;
import za.ac.cput.Service.BookService;

import java.util.List;

@RestController
@RequestMapping("/book")
public class BookController {

    private BookService bookService;

    @Autowired
    public BookController(BookService bookService) {
        this.bookService = bookService;
    }
    @PostMapping("/save")
    public Book save(@RequestBody Book book) {
        return bookService.save(book);
    }
    @PostMapping("/read")
    public Book readBookById(@RequestBody Long bookID) {
        return bookService.readBookById(bookID);
    }
    @PostMapping("/update")
    public Book updateBook(@RequestBody Book book) {
        return bookService.updateBook(book);
    }
    @PostMapping("/delete")
    public void deleteBook(@RequestBody Long bookID) {
        bookService.deleteBook(bookID);
    }
    @PostMapping("/getAll")
    public List<Book> getAllBooks() {
        return bookService.getAllBooks();
    }
}
